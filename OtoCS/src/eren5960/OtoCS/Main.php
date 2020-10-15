<?php

declare(strict_types=1);

namespace eren5960\OtoCS;

use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\block\EnderChest;
use pocketmine\block\tile\TileFactory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use StormGames\Form\economy\EconomySellForm;
use StormGames\SGCore\utils\Utils;

class Main extends PluginBase implements Listener{
	/** @var int[] */
	public static $money_session = [];

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register('otocs', new CSCommand($this));

		TileFactory::register(CSTile::class, ["EnderChest", "minecraft:enderchest"]);
		TileFactory::override(\pocketmine\block\tile\EnderChest::class, CSTile::class);
	}

	protected function onDisable(){
		$this->getConfig()->save();
	}

	public function onInteract(PlayerInteractEvent $event){
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if($block instanceof EnderChest && $this->create($event)){
			$tile = $block->getPos()->getWorld()->getTile($block->getPos());
			if($tile instanceof CSTile && is_string($tile->player) && strtolower($tile->player) === strtolower($player->getName())){
				if(!($player->isSneaking() && $player->getInventory()->getItemInHand()->getId() === ItemIds::HOPPER)){
					$player->setCurrentWindow($tile->getInventory());
					$event->setCancelled();
				}
			}
		}
	}

	public function onInventory(InventoryTransactionEvent $event){
		$trans = $event->getTransaction();
		/** @var SkyPlayer $player */
		$player = $trans->getSource();
		$clear = null;
		foreach($trans->getActions() as $action){
			if($action instanceof SlotChangeAction){
				$inventory = $action->getInventory();
				if($inventory instanceof CSInventory){
					$item = $action->getTargetItem();
					$instrument = NoteInstrument::BASS_DRUM();

					if($item->getId() !== 0 && isset(EconomySellForm::ITEMS[$item->getId()]) && $action->getSourceItem()->getId() === 0){
						$money = EconomySellForm::ITEMS[$item->getId()] * $item->getCount();
						$player->addMoney($money, false);
						self::$money_session[$player->getName()] += $money;
						$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) use ($inventory): void{
							$inventory->clearAll();
						}), 2);
						$instrument = NoteInstrument::PIANO();
					}else{
						$event->setCancelled();
					}
					$player->getWorld()->addSound($player->getPosition(), new NoteSound($instrument, 1));
					break;
				}
			}
		}
	}

	public function create(PlayerInteractEvent $event){
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		$pos = $event->getBlock()->getPos();
		if(isset(CSCommand::$sessions[$player->getName()])){
			$event->setCancelled();
			$tile = $pos->getWorld()->getTile($pos);
			if($tile instanceof CSTile){
				$pos->getWorld()->removeTile($tile);
			}
			/** @var CSTile $tile */
			$tile = TileFactory::create(CSTile::class, $pos->getWorld(), $pos);
			$tile->setName($player->getName() . " : " . CSCommand::$sessions[$player->getName()]);
			$tile->setPlayer($player->getName());
			$pos->getWorld()->addTile($tile);

			$player->sendAlert("Başarılı", "Otomatik Satış Sandığın oluşturuldu. Geriye kalan oluşturma hakkın: §a" . ($player->getMaxCSCont() - $this->getConfig()->get($player->getName(), 0)), "Tamam", "X Kapat");
			unset(CSCommand::$sessions[$player->getName()]);
			return false;
		}
		return true;
	}

	public function onOpen(InventoryOpenEvent $event){
		$inventory = $event->getInventory();
		$name = $event->getPlayer()->getName();
		if($inventory instanceof CSInventory){
			self::$money_session[$name] = 0;
		}
	}

	public function onClose(InventoryCloseEvent $event){
		$inventory = $event->getInventory();
		$player = $event->getPlayer();
		$name = $player->getName();
		if($inventory instanceof CSInventory && self::$money_session[$name] > 0){
			$player->sendTip("§aKazancın\n§8» §f" . Utils::addMonetaryUnit(self::$money_session[$name]) . " §8«");
			unset(self::$money_session[$name]);
		}
	}
}
