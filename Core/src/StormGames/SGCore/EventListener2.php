<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\pass\Pass;
use Eren5960\SkyBlock\SkyPlayer;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;
use pocketmine\entity\Attribute;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\boss\BossEntity;
use StormGames\SGCore\boss\BossManager;
use StormGames\SGCore\enchant\CustomEnchantment;
use StormGames\SGCore\entity\MoneyStatue;
use StormGames\Form\economy\EconomySellForm;
use StormGames\Form\referans\ReferansForm;
use StormGames\SGCore\mission\MissionBlock;
use StormGames\SGCore\mission\MissionEat;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;

class EventListener2 implements Listener{
	/** @var SGCore */
	private $api;

	public function __construct(SGCore $api){
		$this->api = $api;
	}

	// Enchant kodları çalıştır
	public function onArmorChange(InventoryTransactionEvent $event) : void{
		$tr = $event->getTransaction();
		$player = $tr->getSource();
		foreach($tr->getActions() as $action){
			if($action instanceof SlotChangeAction){
				if($action->getInventory() instanceof ArmorInventory){
					$sourceItem = $action->getSourceItem();
					if($player instanceof SGPlayer and !$sourceItem->equals($target = $action->getTargetItem(), false, true)){
						if(!$sourceItem->isNull()){
							CustomEnchantment::runFunction($sourceItem, 'takeOff', $player);
						}
						if(!$target->isNull()){
							CustomEnchantment::runFunction($target, 'putOn', $player);
						}
					}
					break;
				}
			}
		}
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		/** @var SGPlayer $player */
		$player = $event->getPlayer();

		$player->reset(true, false);
		//$player->scoreboard->display(true);
		CustomEnchantment::runFunctionForArmor($player->getArmorInventory(), 'putOn', $player);

		if(!MoneyStatue::$hasSkin){
			foreach($player->getServer()->getWorldManager()->getDefaultWorld()->getEntities() as $entity){
				if($entity instanceof MoneyStatue){
					$entity->updateSkin();
				}
			}
		}

		if($player->firstLogin){
			$player->sendForm(new ReferansForm($player));
		}
	}

	/**
	 * @ignoreCancelled true
	 * @priority LOW
	 *
	 * @param BlockBreakEvent $event
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();

		$event->setXpDropAmount(0);
		if($player->isInLobby() && !$player->isOp()){
			$event->setCancelled();
		}

		if(!$event->isCancelled()){
			if($player->getCurrentMission() instanceof MissionBlock){
				/** @noinspection PhpUndefinedMethodInspection */ // #blameJetbrains
				$player->getCurrentMission()->blockBreak($event->getBlock());
			}
			CustomEnchantment::runFunction($event->getItem(), 'blockBreak', $event);
		}
	}

	// Oto sat
	public function itemEquip(InventoryPickupItemEvent $event){
		foreach($event->getViewers() as $player){
			if($player instanceof SkyPlayer){
				$item = $event->getItem()->getItem()->getId();
				if(($count = Utils::getItemCount($event->getItem()->getId(), $event->getInventory())) >= 64){
					if(isset(EconomySellForm::ITEMS[$item]) && in_array($item, $player->otoSellIds)){
						$player->addMoney(EconomySellForm::ITEMS[$item] * $count, false);
						$player->getInventory()->remove($item = $event->getItem()->getItem());
						$player->sendPopup("§ex" . $count . " " . $item->getName() . " satıldı.");
					}
				}
			}
		}
	}

	/**
	 * @ignoreCancelled true
	 * @priority LOW
	 *
	 * @param BlockPlaceEvent $event
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void{
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		if($player->isInLobby() && !$player->isOp()){
			$event->setCancelled();
		}
		if(!$event->isCancelled()){
			if($player->getCurrentMission() instanceof MissionBlock){
				/** @noinspection PhpUndefinedMethodInspection */ // #blameJetbrains
				$player->getCurrentMission()->blockPlace($event->getBlock());
			}
		}
	}

	/**
	 * @ignoreCancelled true
	 * @priority LOW
	 *
	 * @param BlockSpreadEvent $event
	 */
	public function onBlockSpread(BlockSpreadEvent $event) : void{
		if($event->getBlock()->getPos()->getWorld()->getId() === Server::getInstance()->getWorldManager()->getDefaultWorld()->getId()){
			$event->setCancelled();
		}
	}

	/**
	 * @ignoreCancelled true
	 * @priority LOW
	 *
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event) : void{
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			if(!$event->isCancelled()){
				if($event->getItem()->getId() === ItemIds::MAGMA_CREAM and $event->getItem()->getNamedTag()->hasTag('estate', StringTag::class) !== null){
					RealEstateManager::startPaste($player, $event->getBlock(), $event->getItem());
					$event->setCancelled();
				}
			}
		}
	}

	/**
	 * @ignoreCancelled true
	 * @priority LOWEST
	 *
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof SkyPlayer && $entity->isInLobby()){
			$event->setCancelled();
		}
	}

	/**
	 * @ignoreCancelled true
	 * @priority LOWEST
	 *
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onAttack(EntityDamageByEntityEvent $event) : void{
		if(($magic = $event->getCause() === $event::CAUSE_MAGIC) && $event->getBaseDamage() > 5){
			$event->setBaseDamage($event->getBaseDamage() / 2);
		}
		$player = $event->getEntity();
		$attacker = $event->getDamager();

		if($player->getWorld()->getFolderName() === "arena"){
			if($player instanceof SGPlayer){
				if($attacker instanceof SGPlayer){
					if(BossManager::haveBoss()){
						$event->setCancelled();
						return;
					}
					if(!$magic){
						CustomEnchantment::runFunction($attacker->getInventory()->getItemInHand(), 'attack', $attacker, $player, $event);
						CustomEnchantment::runFunctionForArmor($player->getArmorInventory(), 'onDamage', $player, $event);
						return;
					}
				}else{
					return;
				}
			}elseif($player instanceof BossEntity){
				return;
			}
		}elseif($attacker instanceof MonsterBase || ($attacker instanceof SkyPlayer && ($attacker->isOp() ||
				($player instanceof CreatureBase && $attacker->isInIsland() && $attacker->getNowIsland()->handleChange($attacker, Pass::MOB_ACTION))))){
			return;
		}

		$event->setCancelled();
	}

	public function onRespawn(PlayerRespawnEvent $event) : void{
		$event->setRespawnPosition($event->getPlayer()->getWorld()->getSpawnLocation());
	}

	public function onDeath(PlayerDeathEvent $event) : void{
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		if($player->isInArena()){
			$player->addDeath(1, false);
			$player->reduceMoney(20, false);

			static $deny = [
				EntityDamageEvent::CAUSE_SUFFOCATION,
				EntityDamageEvent::CAUSE_LAVA,
				EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
				EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
				EntityDamageEvent::CAUSE_SUICIDE,
				EntityDamageEvent::CAUSE_STARVATION
			];
			/** @var SGPlayer $murder */
			$murder = $player->getLastDamageCause() !== null ? (!in_array($player->getLastDamageCause()->getCause(), $deny, true) ? $player->getLastAttacker() : null) : null;

			if($murder !== null and $murder->isConnected()){
				$translateArgs = [
					'player.dead.message.damager',
					[
						TextFormat::RED . $player->getName() . TextFormat::GRAY,
						TextFormat::GREEN . $murder->getName() . TextFormat::GRAY,
						Utils::getItemName($murder->getInventory()->getItemInHand()) . TextFormat::GRAY
					]
				];
				$murder->onKill();
			}else{
				$translateArgs = [
					'player.dead.message',
					[TextFormat::RED . $player->getName() . TextFormat::GRAY]
				];
			}

			/** @var SGPlayer $receiver */
			foreach($player->getWorld()->getPlayers() as $receiver){
				if($receiver->isConnected()){
					$receiver->sendMessage(Prefix::PURE . $receiver->translate(...$translateArgs));
				}
			}

			$player->setLastAttacker(null);
		}

		$event->setDeathMessage('');
		$event->setXpDropAmount(0);
		$event->setKeepInventory(true);
	}

	public function onEat(PlayerItemConsumeEvent $event) : void{
		/** @var SGPlayer $player */
		$player = $event->getPlayer();
		if($player->getCurrentMission() instanceof MissionEat){
			/** @noinspection PhpUndefinedMethodInspection */ // #blameJetbrains
			$player->getCurrentMission()->eat($event->getItem());
		}
	}

	/*public function onEntitySpawn(EntitySpawnEvent $event) : void{
		if($event->getEntity() instanceof ExperienceOrb || $event->getEntity() instanceof ExperienceBottle){
			$event->getEntity()->flagForDespawn();
		}
	}*/

	public function onEntityTeleport(EntityTeleportEvent $event) : void{
		$player = $event->getEntity();
		$from = $event->getFrom()->getWorld();
		$to = $event->getTo()->getWorld();
		if($player instanceof SkyPlayer && $to !== null && $to->getId() !== $from->getId()){
			// action
		}
	}
}