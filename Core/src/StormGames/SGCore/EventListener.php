<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use Frago9876543210\Specter\Specter;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\player\GameMode;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Chat\ChatFilter;
use StormGames\Prefix;
use StormGames\SGCore\entity\CommandHuman;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\permission\GroupManager;
use StormGames\SGCore\plugin\StormGamesPlugin;

class EventListener implements Listener{
	public const CHAT_FLOOD_TIME = 1;

	/** @var SGCore */
	protected $core;
	/** @var string[] */
	private $devices = [];

	public function __construct(SGCore $core){
		$this->core = $core;
	}

	public function onEnable(PluginEnableEvent $event) : void{
		$plugin = $event->getPlugin();
		if($plugin instanceof StormGamesPlugin){
			$this->core->getLogger()->info(TextFormat::GREEN . $plugin->getDescription()->getName() . " eklentisi aktif!");
		}
	}

	/**
	 * @param PlayerCreationEvent $event
	 *
	 * @priority HIGH
	 */
	public function onPlayerCreate(PlayerCreationEvent $event) : void{
		$event->setBaseClass(SGPlayer::class);
		if($event->getPlayerClass() === Player::class){
			throw new \RuntimeException("Oyuncu sınıfı ayarlanmamış");
		}
	}

	/**
	 * @param PlayerPreLoginEvent $event
	 *
	 * @priority LOW
	 */
	public function onPreLogin(PlayerPreLoginEvent $event) : void{
		if(in_array($event->getIp(), SGCore::PROXY_SERVERS)){
			$event->setKickReason(PlayerPreLoginEvent::KICK_REASON_BANNED, "You use proxy server");
		}
		if($event->isKickReasonSet(PlayerPreLoginEvent::KICK_REASON_SERVER_FULL)){
			$result = SGCore::getDatabase()->query('SELECT permGroup FROM players WHERE username=\'' . strtolower($event->getPlayerInfo()->getUsername()) . '\'');
			if(($result->num_rows ?? 0) !== 0){
				$group = GroupManager::getGroup($result->fetch_assoc()['permGroup']);
				if($group->getPermissions()[DefaultPermissions::MVP] ?? false){
					$event->clearKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_FULL);
				}
			}
		}
	}

	public function onPacketReceived(DataPacketReceiveEvent $event) : void{
		$pk = $event->getPacket();
		if($pk instanceof LoginPacket){
			static $device = [
				'Unknown',
				'Android',
				'iOS',
				'macOS',
				'FireOS',
				'GearVR',
				'HoloLens',
				'Windows 10',
				'Windows',
				'Dedicated',
				'tvOS',
				'Orbis',
				'NX'
			];
			$this->devices[$pk->extraData->displayName] = $pk->clientData->DeviceModel . ' (' . ($device[$pk->clientData->DeviceOS] ?? 'Unknown') . ')';
		}
	}

	public function onLogin(PlayerLoginEvent $event) : void{
		/** @var SGPlayer $player */
		$db = SGCore::getDatabase();
		$player = $event->getPlayer();
		$player->loadCriminalRecord($db);

		$player->getCriminalRecord()->check();
		if($player->getCriminalRecord()->isBanned()){
			$event->setKickMessage($player->getCriminalRecord()->getBanMessage());
			$event->setCancelled();
			return;
		}
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		/** @var SGPlayer $player */
		$player = $event->getPlayer();
		$pos = $player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation();
		$player->teleport($pos);
		$player->setSpawn($pos);

		$player->loadSGPlayer(SGCore::getDatabase());
		$groupTime = $player->getGroup()->getTime();
		if($groupTime !== 0 and $groupTime - time() <= 0){
			$player->setGroup(GroupManager::getGroup(GroupManager::getDefaultGroup()));
		}

		$player->joinTime = time();
		$player->device = $this->devices[$player->getName()];

		unset($this->devices[$player->getName()]);

		//$player->setGamemode(GameMode::ADVENTURE());

		$event->setJoinMessage('');
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		/** @var SGPlayer $player */
		$player = $event->getPlayer();

		AntiCheat::removeFromAntiCheats($player);
		$player->onQuit();
		$event->setQuitMessage('');
	}

	/**
	 * @param PlayerChatEvent $event
	 *
	 * @priority LOW
	 */
	public function onChat(PlayerChatEvent $event) : void{
		/** @var SGPlayer $player */
		$player = $event->getPlayer();
		$message = $event->getMessage();
		if($player->getGroup() === null) return; // HACK???

		if(!$player->hasPermission(DefaultPermissions::CHAT_BYPASS)){
			if(time() - $player->getLastChatTime() <= self::CHAT_FLOOD_TIME){
				$player->sendMessage(Prefix::CHAT() . TextFormat::RED . $player->translate('chat.slowdown'));
				$event->setCancelled();
				return;
			}

			$filter = $player->getCore()->getChatFilter()->check($message);
			if($filter !== ChatFilter::CHAT_NONE){
				$player->sendMessage(Prefix::CHAT() . TextFormat::RED . $player->translate('chat.dont.swear.or.advertise'));
				$event->setCancelled();
				return;
			}

			$message = mb_strtolower($message, 'utf-8');
		}

		$player->setLastChatTime(time());

		if(!$player->hasPermission(DefaultPermissions::CHAT_USE_COLORS)){
			$message = TextFormat::clean($message);
		}

		if(!SGCore::$chatForAll){
			$recipients = $player->getWorld()->getPlayers();
			$recipients[] = SGCore::getAPI()->console;
			$event->setRecipients($recipients);
		}
		$event->setFormat($player->getGroup()->convertChatFormat($player, $message));
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @priority LOW
	 */
	public function onInteract(PlayerInteractEvent $event) : void{
		/** @var SGPlayer $player */
		$player = $event->getPlayer();
		$item = $event->getItem();

		if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
			AntiCheat::updateBreakTime($player);
		}

		/*if($player->isAvailable() and $item->hasCustomName()/* and $player->canClick($action)){
			switch($item->getId()){
				case ItemIds::DYE:
					switch($item->getMeta()){
						case 10: // green
							$player->setVisibleStatus(SGPlayer::VISIBLE_STAFFS);
							$player->getInventory()->setItemInHand(ItemFactory::get(ItemIds::DYE, 5)->setCustomName(TextFormat::RESET . TextFormat::GREEN . $player->translate('items.lobby.visible', [TextFormat::GRAY . $player->translate('visible.staffs')])));
							$player->sendMessage(Prefix::MAIN . $player->translate('see.only.staff'));
							break;
						case 5: // purple
							$player->setVisibleStatus(SGPlayer::VISIBLE_NONE);
							$player->getInventory()->setItemInHand(ItemFactory::get(ItemIds::DYE, 8)->setCustomName(TextFormat::RESET . TextFormat::GREEN . $player->translate('items.lobby.visible', [TextFormat::GRAY . $player->translate('visible.none')])));
							$player->sendMessage(Prefix::MAIN . $player->translate('see.any.players'));
							break;
						case 8: // gray
							$player->setVisibleStatus(SGPlayer::VISIBLE_ALL);
							$player->getInventory()->setItemInHand(ItemFactory::get(ItemIds::DYE, 10)->setCustomName(TextFormat::RESET . TextFormat::GREEN . $player->translate('items.lobby.visible', [TextFormat::GRAY . $player->translate('visible.everyone')])));
							$player->sendMessage(Prefix::MAIN . $player->translate('see.all.players'));
							break;
					}
					break;
				/*case ItemIds::ENDER_CHEST:
					$class = SGCore::$formClasses['shop'];
					$player->sendForm(new $class($player));
					break;
				case ItemIds::SKULL:
					$class = SGCore::$formClasses['menu'];
					$player->sendForm(new $class($player));
					break;
			}

			$event->setCancelled();
		}*/
	}


	/**
	 * @ignoreCancelled true
	 * @priority LOW
	 *
	 * @param EntityDamageByEntityEvent $event
	 */
	public function onDamage(EntityDamageByEntityEvent $event) : void{
		$entity = $event->getEntity();
		if($entity instanceof SGPlayer and $event->getDamager() instanceof SGPlayer and $event->getDamager()->getId() !== $entity->getId()){
			/* @noinspection PhpParamsInspection */ # blameJetbrains
			$entity->setLastAttacker($event->getDamager());
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		if(!$event->getItem()->getNamedTag()->hasTag("fake", ByteTag::class)){
			AntiCheat::checkInstaBreak($event);
		}
	}

	public function onQuery(QueryRegenerateEvent $event) : void{
		//$event->setMaxPlayerCount($event->getPlayerCount() + 1);
		$plugin = function($name) : Plugin{
			return Server::getInstance()->getPluginManager()->getPlugin($name);
		};
		$event->setPlugins([
			$plugin("SG-Core"),
			$plugin("SkyBlock"),
			$plugin('DevTools')
		]);
	}

	public function onPlayerExhaust(PlayerExhaustEvent $event){
		$event->setAmount($event->getAmount() / 2);
	}

	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		if($event->getFrom()->distance($event->getTo()) < 0.1){
			return;
		}
		foreach($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(16, 2, 16), $player) as $entity){
			if(!$entity instanceof CommandHuman){
				continue;
			}
			$entity->lookAtInto($player);
		}
	}
}