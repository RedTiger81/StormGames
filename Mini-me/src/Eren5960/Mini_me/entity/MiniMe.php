<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 01 Nisan 2020
 */
declare(strict_types=1);
 
namespace Eren5960\Mini_me\entity;
 
use Eren5960\Mini_me\Main;
use Eren5960\Mini_me\MCmd;
use Eren5960\SkyBlock\SkyPlayer;
use jojoe77777\FormAPI\SimpleForm;
use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\PunchBlockParticle;
use pocketmine\world\Position;
use pocketmine\world\World;
use StormGames\SGCore\enchant\DragonPlus;

abstract class MiniMe extends Human{
	/** @var int */
	public $level = 1;
	/** @var null|string */
	public $owner = null;
	/** @var int */
	public $created = 1;
	/** @var string|null */
	public $type = null;
	/** @var Block */
	public $block = null;
	public $breakTime = 1;
	public $currentBreak = -1;
	public $item = null;

	abstract public function onSuccess(): void;
	abstract public function getBlockIds(): array;
	abstract public function getHandItem(): Item;

	public function getOwner(): ?Player{
		return Server::getInstance()->getPlayerExact($this->owner);
	}

	public function controlBlock(Block $block): bool{
		return in_array($block->getId(), $this->getBlockIds());
	}

	public function getArea(): int{
		return [2, 3, 4][$this->level-1];
	}

	public function getTypeOfInventory(): string{
		return [InvMenu::TYPE_HOPPER, InvMenu::TYPE_CHEST, InvMenu::TYPE_DOUBLE_CHEST][$this->level-1];
	}

	public function breakProgress(int $currentTick){
		if($this->breakTime <= $this->currentBreak){
			$this->stopBreakBlock();
		}else{
			$this->attackBlock();
			$this->continueBreakBlock();
		}
	}

	public function onUpdate(int $currentTick) : bool{
		if(($player = $this->getOwner()) instanceof SkyPlayer && $player->getWorld()->getId() === $this->getWorld()->getId()){
			if($this->block === null){
				foreach(DragonPlus::getBlocks($this->location, $this->getArea()) as $block){
					if($this->controlBlock($block)){
						$this->block = $block;
						$this->lookAtInto($block->getPos());
						break;
					}
				}
			}elseif(!in_array($this->getWorld()->getBlock($this->block->getPos())->getId(), $this->getBlockIds())){
				$this->block = null;
			}

			if($this->block !== null){
				$this->breakProgress($currentTick);
			}
		}
		return parent::onUpdate($currentTick);
	}

	public function attackBlock() : void{
		$this->broadcastEntityEvent(ActorEventPacket::ARM_SWING, null, $this->getViewers());
		$this->breakTime = ceil($this->block->getBreakInfo()->getBreakTime($this->getHandItem()) * 20);

		if($this->breakTime > 0){
			$this->getWorld()->broadcastLevelEvent($this->block->getPos(), LevelEventPacket::EVENT_BLOCK_START_BREAK, (int) (65535 / $this->breakTime));
		}
	}

	public function continueBreakBlock() : void{
		$this->getWorld()->addParticle($this->block->getPos(), new PunchBlockParticle($this->block, 1));
		$this->currentBreak++;
	}

	public function stopBreakBlock() : void{
		$this->getWorld()->broadcastLevelEvent($this->block->getPos(), LevelEventPacket::EVENT_BLOCK_STOP_BREAK);
		$this->onSuccess();
		$this->block = null;
		$this->currentBreak = -1;
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}elseif($source instanceof EntityDamageByEntityEvent){
			$damager = $source->getDamager();
			if($damager instanceof SkyPlayer && strtolower($this->owner) === strtolower($damager->getName())){
				$this->sendForm($damager);
			}
		}
		$source->setCancelled();
	}

	public function sendForm(SkyPlayer $player): void{
		$form =	new SimpleForm(function(SkyPlayer $player, ?int $i){
			if($i === null || $this->isFlaggedForDespawn()) return;
			if($i===0){
				$this->sendInventory($player);
			}elseif($i===1){
				$this->flagForDespawn();
				$player->getInventory()->addItem(MCmd::getItem($this->type, $this->owner, $this));
			}else{
				$this->sendUpdateForm($player);
			}
		});
		$form->setTitle($this->getNameTag());
		$form->setContent("§7Çırağını buradan yönetebilirsin.");
		$form->addButton("§2Envantere bak");
		$form->addButton("§2Başka bir yere taşı");
		$form->addButton("§3Geliştir"); // TODO : İcon eklenecek
		$form->sendToPlayer($player);
	}

	public function sendUpdateForm(SkyPlayer $player): void{
		if($this->level === 3){
			$player->sendMessage("§7» §cMaximum seviyeye ulaştın.");
		}else{
			$form = new SimpleForm(function(SkyPlayer $player, ?int $i){
				if($i === null || $i === 1){
					$this->sendForm($player);
					return;
				}
				if($player->getCoins() >= 5){
					$player->sendAlert("BAŞARI", "Tebrikler! Yeni bir seviye açtın. Artık çırağın daha hızlı ve güçlü", "Yaşasın", "Kapat");
					$player->reduceMoney(5);
					$this->level += 1;
					$this->setScoreTag("§8» §f" . $this->level . ". Seviye §8«");
					$this->item = null;
					$this->getInventory()->setItemInHand($this->getHandItem());
				}else{
					$player->sendAlert("HATA", "Yeterli nakitin yok! Web Panelden nakit satın alabilirsin.\n§8» §bstormgames.net", "Hemen alıyorum", "X Kapat");
				}
			});
			$form->setTitle($this->getScoreTag());
			$form->setContent("§7Şuanki seviye: §f" . $this->level . "\n" . "§7Yeni seviyede olacaklar: \n- §aDaha hızlı blok kırma & koyma.\n§7- §aDaha büyük envanter.\n§7- §aBlok mesafesi artar. (Uzaktaki bloklar)");
			$form->addButton("§3Geliştir\n§8» §3" . 5 . " Nakit §8«");
			$form->addButton("< Geri dön");
			$form->sendToPlayer($player);
		}
	}

	public function sendInventory(SkyPlayer $player){
		$menu = InvMenu::create($this->getTypeOfInventory());
		$item = $this->getInventory()->getItemInHand();
		$menu->setName($this->getNameTag())->setInventoryCloseListener(function(Player $player, InvMenuInventory $baseFakeInventory) use($item){
			if($this->inventory !== null){
				$cache = $baseFakeInventory->getItem(0);
				$this->inventory->setContents($baseFakeInventory->getContents());
				$this->inventory->setItemInHand($item);
				$this->inventory->addItem($cache);
			}
		});
		$inventory = clone $this->getInventory();
		$inventory->setItemInHand(ItemFactory::get(0));
		$menu->getInventory()->setContents($inventory->getContents());

		if($this->getTypeOfInventory() === InvMenu::TYPE_DOUBLE_CHEST){ // control index
			$range = range($menu->getInventory()->getSize()-18, $menu->getInventory()->getSize()-1);
			foreach($range as $i){
				$menu->getInventory()->setItem($i, ItemFactory::get(ItemIds::BARRIER));
			}
			$menu->setListener(function(Player $player, Item $in, Item $out){return !($in->getId() === ItemIds::BARRIER || $out->getId() === ItemIds::BARRIER);});
		}
		$menu->send($player);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->type = $nbt->getString(Main::NBTP . 'type');
		$this->level = $nbt->getInt(Main::NBTP . 'level');
		$this->created = $nbt->getInt(Main::NBTP . 'created');
		$this->owner = $nbt->getString(Main::NBTP . 'owner');

		$this->setNameTag(Main::NAMES[$this->type]);
		$this->setScoreTag("§8» §f" . $this->level . ". Seviye §8«");
		$this->setNameTagAlwaysVisible(true);
		$this->setScale(0.8);
		$this->getInventory()->setItemInHand($this->getHandItem());
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setString(Main::NBTP . "owner", $this->owner);
		$nbt->setInt(Main::NBTP . 'created', $this->created);
		$nbt->setInt(Main::NBTP . 'level', $this->level);
		$nbt->setString(Main::NBTP . 'type', $this->type);
		return $nbt;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function lookAtInto(Position $target) : void{
		$xDist = $target->x - $this->location->x;
		$zDist = $target->z - $this->location->z;

		$horizontal = sqrt($xDist ** 2 + $zDist ** 2);
		$vertical = ($target->y - $this->location->y) + 0.55;
		$this->location->pitch = atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down
		$this->location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($this->location->yaw < 0){
			$this->location->yaw += 360.0;
		}
		$this->updateMovementInto($target->getWorld());
	}

	protected function updateMovementInto(World $world){
		$pk = new MoveActorAbsolutePacket();
		$pk->entityRuntimeId = $this->id;
		$pk->position = $this->getOffsetPosition($this->location);
		$pk->xRot = $this->location->pitch;
		$pk->yRot = $this->location->yaw;
		$pk->zRot = $this->location->yaw;
		$world->broadcastGlobalPacket($pk);
	}

	public function updateMovement(bool $teleport = false) : void{}

	public function getTagOfInventory(): ListTag{
		$inventoryTag = new ListTag([], NBT::TAG_Compound);
		if($this->inventory !== null){
			//Normal inventory
			$slotCount = $this->inventory->getSize() + $this->inventory->getHotbarSize();
			for($slot = $this->inventory->getHotbarSize(); $slot < $slotCount; ++$slot){
				$item = $this->inventory->getItem($slot - 9);
				if(!$item->isNull()){
					$inventoryTag->push($item->nbtSerialize($slot));
				}
			}

			//Armor
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->armorInventory->getItem($slot - 100);
				if(!$item->isNull()){
					$inventoryTag->push($item->nbtSerialize($slot));
				}
			}
		}
		return $inventoryTag;
	}

	protected function onDeath() : void{
		$player = $this->getOwner();
		if($player instanceof SkyPlayer){
			$player->getInventory()->addItem(MCmd::getItem($this->type, $this->owner, $this));
		}
		parent::onDeath();
	}
}