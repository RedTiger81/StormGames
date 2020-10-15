<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use FolderPluginLoader\FolderPluginLoader;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\EntityFactory;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\utils\TextFormat;
use StormGames\Chat\BroadcastManager;
use StormGames\Chat\ChatFilter;
use StormGames\Crate\CrateContents;
use StormGames\SGCore\entity\FactionFloatText;
use StormGames\SGCore\entity\MoneyStatue;
use StormGames\SGCore\entity\TopKillsFloatingText;
use StormGames\Form\VoteForm;
use StormGames\SGCore\entity\TopMoneyFloatText;
use StormGames\Form\ProfileFactionForm;
use StormGames\SGCore\boss\BossManager;
use StormGames\SGCore\commands\AnvilCommand;
use StormGames\SGCore\commands\EconomyCommand;
use StormGames\SGCore\commands\KitCommand;
use StormGames\SGCore\commands\MissionCommand;
use StormGames\SGCore\commands\RankCommand;
use StormGames\SGCore\commands\RealEstateCommand;
use StormGames\SGCore\commands\TopsCommand;
use StormGames\SGCore\commands\TPACommand;
use StormGames\SGCore\commands\TutorialCommand;
use StormGames\SGCore\commands\VIPCommand;
use StormGames\SGCore\commands\WarpCommand;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\SGCore\item\ItemManager;
use StormGames\SGCore\mission\Mission;
use StormGames\Form\PlayerMenuForm;
use StormGames\Form\RemotePlayerForm;
use StormGames\Helper\HelperManager;
use StormGames\Kit\KitManager;
use StormGames\Particle\Particle;
use StormGames\Pet\PetManager;
use StormGames\Rank\RankManager;
use StormGames\SGCore\blocks\BlockManager;
use StormGames\SGCore\commands\BanCommand;
use StormGames\SGCore\commands\BroadcastCommand;
use StormGames\SGCore\commands\EvalCommand;
use StormGames\SGCore\commands\FloatingTextSpawnCommand;
use StormGames\SGCore\commands\GroupCommand;
use StormGames\SGCore\commands\ListCommand;
use StormGames\SGCore\commands\MenuCommand;
use StormGames\SGCore\commands\MessageCommand;
use StormGames\SGCore\commands\ModeratorCommand;
use StormGames\SGCore\commands\MusicCommand;
use StormGames\SGCore\commands\NPCCommand;
use StormGames\SGCore\commands\PingCommand;
use StormGames\SGCore\commands\PromotionCommand;
use StormGames\SGCore\commands\RemotePlayerCommand;
use StormGames\SGCore\commands\StopCommand;
use StormGames\SGCore\commands\TestCommand;
use StormGames\SGCore\commands\VoteCommand;
use StormGames\SGCore\commands\WorldCommand;
use StormGames\SGCore\commands\XYZCommand;
use StormGames\SGCore\commands\BookCommand;
use StormGames\SGCore\entity\EntityManager;
use StormGames\SGCore\entity\utils\Skins;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\permission\GroupManager;
use StormGames\SGCore\plugin\StormGamesPlugin;
use StormGames\SGCore\task\CleanerTask;
use StormGames\SGCore\task\StopServerTask;
use StormGames\SGCore\tiles\TileManager;
use StormGames\SGCore\utils\MySQL;
use StormGames\SGCore\utils\MySQLValidator;
use StormGames\SGCore\utils\TextUtils;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\utils\IconUtils;

/***
NOTE: Eğer minigame sunucusu ise bazı anticheatleri devre dışı bırak.
 */
class SGCore extends StormGamesPlugin{
	public const MONETARY_UNIT = '$';
	public const SERVER_NAME_FORMAT = TextFormat::BOLD . TextFormat::AQUA. 'Storm' . TextFormat::WHITE . 'Games' . TextFormat::DARK_GRAY . ' » ';

	public const DISCORD = "dc.stormgames.net";
	public const INSTAGRAM_AND_TWITTER = "@stormgames.network";

	public const MYSQL_USERNAME = "root";
	public const MYSQL_PASSWORD = "Eren.59.Tuna";
	public const MYSQL_DBNAME = "skyblock";
	public const MYSQL_IP = "45.139.200.45";
	public const MYSQL_PORT = 3306;

	public const PROXY_SERVERS = ['206.189.255.167'];

	private const UNREGISTERED_COMMANDS = [
		"ban",
		"ban-ip",
		"banlist",
		"checkperm",
		//"clear",
		"defaultgamemode",
		"difficulty",
		"effect",
		"enchant",
		//"gamemode",
		//"kill",
		//"kick",
		"list",
		"me",
		//"op",
		"pardon",
		"pardon-ip",
		"particle",
		"plugins",
		"reload",
		"save-all",
		"save-off",
		"save-on",
		"say",
		"seed",
		"stop",
		"spawnpoint",
		"tell",
		"title",
		"transferserver",
		"version",
	];

	/** @var SGCore */
	private static $api;
	/** @var MySQL */
	protected $database;
	/** @var ChatFilter */
	protected $chatFilter;

	/** @var ConsoleCommandSender */
	public $console;

	public static $formClasses = [
		"menu" => PlayerMenuForm::class,
		"remotePlayer" => RemotePlayerForm::class
	];

	/** @var bool */
	public static $chatForAll = false;

	public function onLoad(){
		self::$api = $this;
	}

	public function onEnable(){
		parent::onEnable();
		$this->init();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener2($this), $this);
		$this->getScheduler()->scheduleDelayedRepeatingTask(new StopServerTask($this->getServer()), 144000, 20); // 2 hours

		$this->unregisterCommands();
		$this->getServer()->getCommandMap()->registerAll("stormgames-core", [
			new BanCommand('ban'),
			new BroadcastCommand('broadcast'),
			new EvalCommand('eval'),
			new GroupCommand('group'),
			new ListCommand('list'),
			new MenuCommand('menu'),
			new MessageCommand('msg'),
			new ModeratorCommand('mod'),
			new MusicCommand('music'),
			new NPCCommand('npc'),
			new PingCommand('ping'),
			new PromotionCommand('promotion'),
			new RemotePlayerCommand('rp'),
			new StopCommand('stop'),
			//new WhatsNewCommand("whatsnew"),
			new WorldCommand('world'),
			new TestCommand('sgtest'),
			new VoteCommand('vote'),
			new XYZCommand('xyz'), 
			new BookCommand('book'),
			new FloatingTextSpawnCommand('ft')                          ,
			new AnvilCommand('a'),
			new EconomyCommand('e'),
			new MissionCommand('mission'),
			new RealEstateCommand('realestate'),
			new TopsCommand('tops'),
			new VIPCommand('vip'),
			new WarpCommand('warp'),
			new TutorialCommand('tutorial'),
			new TPACommand('tpa'),
			new KitCommand("kit"),
			new RankCommand('rank')
		]);
		$this->getServer()->getPluginManager()->loadPlugins($this->getFile() . 'plugins', [FolderPluginLoader::class]);
        $this->getServer()->enablePlugins(PluginLoadOrder::POSTWORLD());
	}

	private function init() : void{
		$this->chatFilter = new ChatFilter($this);
		$this->console = new ConsoleCommandSender();

		Language::init();
		Skins::init();
		DefaultPermissions::loadDefaultPermissions();
		GroupManager::init();
		$this->loadDatabase();
		TileManager::init();
		ItemManager::init();
		BlockManager::init();
		Particle::init();
		PetManager::init();
		CrateContents::init();
		PromotionManager::init();
		TextUtils::init();
		Utils::init($this->getServer());
		MusicManager::init($this);
		EntityManager::init();
		BroadcastManager::init();
		IconUtils::init($this);
		HelperManager::init();
		RankManager::init();
		EnchantManager::init();
		Mission::init();
		RealEstateManager::init();
		KitManager::init();
		if(!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
		BossManager::init();
		$this->getScheduler()->scheduleRepeatingTask(new CleanerTask($this->getServer()->getWorldManager()), 1200 * 10);

		$this->initEntity();
		$this->initBroadcast();
		$this->updateServerName();

		SGCore::$formClasses['remotePlayer'] = RemotePlayerForm::class;
		TopMoneyFloatText::$list = Top::money();
	}

	private function unregisterCommands() : void{
		$map = $this->getServer()->getCommandMap();
		foreach(self::UNREGISTERED_COMMANDS as $commandName){
			$command = $map->getCommand($commandName);
			if($command !== null){
				$map->unregister($command);
			}
		}
	}

	public function onDisable(){
		/** @var SGPlayer $player */
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$player->onQuit();
		}
	}

	protected function loadDatabase() : void{
		$this->database = new MySQL(self::MYSQL_IP, self::MYSQL_USERNAME, self::MYSQL_PASSWORD, self::MYSQL_DBNAME, self::MYSQL_PORT);
		$playerNameCallback = function(SGPlayer $player) : string{ return $player->getLowerCaseName(); };
		$validator = MySQLValidator::new(self::TABLE_PLAYERS);
		$validator->add('username')->text()->defaultPlayer($playerNameCallback);
		$validator->add('name')->text()->defaultPlayer(function(SGPlayer $player) : string{ return $player->getName(); });
		$validator->add('biography')->text()->default('I <3 StormGames');
		$validator->add('coins')->int()->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validator->add('crateKeys')->text()->callback(function(string $data){ return TextUtils::toArray($data, true); });
		$validator->add('groupTime')->int()->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validator->add('language')->text()->defaultPlayer(function(SGPlayer $player){ return Language::getLanguage($player->getLocale()); });
		$validator->add('lastDevice')->text()->defaultPlayer(function(SGPlayer $player) : string{ return $player->getDevice(); });
		$validator->add('permissions')->text()->callback(function(string $data){
			$permissions = TextUtils::toArray($data);
			if(!empty($permissions)){
				foreach(($permissions = array_flip($permissions)) as $key => $value){
					$positive = substr($key, 0, 1) !== "-";
					$permissions[$positive ? $key : substr($key, 1)] = $positive;
				}
			}

			return $permissions;
		});
		$validator->add('permGroup')->text()->default(GroupManager::getDefaultGroup());
		$validator->add('levels')->text()->callback(function(string $data){ return TextUtils::toArray($data, true); });;
		$validator->add('timePlayed')->int()->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validator->add('listenMusic')->int()->default(1);
		$validator->add('kills')->int()->callback(TextUtils::class . '::toNumber');
		$validator->add('deaths')->int()->callback(TextUtils::class . '::toNumber');
		$validator->add('money')->int()->default(5000)->callback(TextUtils::class . '::toNumber');
		$validator->add('kitTime')->int()->callback(TextUtils::class . '::toNumber');
		$validator->add('rankId')->int()->callback(TextUtils::class . '::toNumber')->default(-1);
		$validator->add('xp')->text()->callback(TextUtils::class . '::toNumber');
		$validator->add('sellItemIds')->text()->callback(function(string $data){return TextUtils::toArray($data);});
		$validator->createTable($this->database);

		$validatorCr = MySQLValidator::new(self::TABLE_CRIMINAL_RECORDS);
		$validatorCr->add('username')->text()->defaultPlayer(function(SGPlayer $player) : string{ return $player->getLowerCaseName(); });
		$validatorCr->add('banInfo')->text()->default('0:null:0:null')->callback(function($data){ return array_map('\StormGames\SGCore\utils\TextUtils::toNumber', explode(':', $data, 4)); }); // banned:reason:time:bannedBy
		$validatorCr->add('banCount')->int()->default(0)->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validatorCr->add('kickCount')->int()->default(0)->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validatorCr->createTable($this->database);

		$validatorPr = MySQLValidator::new(self::TABLE_PROMOTIONS);
		$validatorPr->add('code')->text();
		$validatorPr->add('usedPlayers')->text()->callback('\StormGames\SGCore\utils\TextUtils::toArray');
		$validatorPr->add('usableCount')->int()->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validatorPr->add('coins')->int()->callback('\StormGames\SGCore\utils\TextUtils::toNumber');
		$validatorPr->createTable($this->database);

		$validator = MySQLValidator::new(self::TABLE_SKYBLOCK_MISSIONS);
		$validator->add('username')->text()->defaultPlayer($playerNameCallback);
		$validator->add('currentMissionId')->int()->callback(TextUtils::class . '::toNumber')->default(-1);
		$validator->add('startDate')->int()->callback(TextUtils::class . '::toNumber')->default(function(){ return time(); });
		$validator->add('completedMissions')->text()->callback(function(string $data){ return TextUtils::toArray($data); });
		$validator->createTable($this->database);
	}

	/**
	 * @return MySQL
	 */
	public static function getDatabase() : MySQL{
		return self::$api->database;
	}

	/**
	 * @return ChatFilter
	 */
	public function getChatFilter() : ChatFilter{
		return $this->chatFilter;
	}

	/**
	 * @return SGCore
	 */
	public static function getAPI() : SGCore{
		return self::$api;
	}

	private function initEntity() : void{
		/** Entity Register */
		EntityFactory::register(MoneyStatue::class, []);
		EntityFactory::register(FactionFloatText::class, []);
		EntityFactory::register(\StormGames\SGCore\entity\TopMoneyFloatText::class, []);
		EntityFactory::register(TopKillsFloatingText::class, []);

		// Extra NPC
		NPCCommand::$npcExtraArg["zengin"] = function (SGPlayer $player, array $args) : void{
			$queue = intval($args[1] ?? 1);
			if($queue <= 0){
				$player->sendMessage("§cAq salağı 1'den büyük olacak"); //bu ne amk edit: tuna
			}else{
				$nbt = EntityFactory::createBaseNBT($player->getLocation(), null, $player->getLocation()->yaw, $player->getLocation()->pitch);
				$nbt->setInt("Queue", $queue);
				$entity = new MoneyStatue($player->getWorld(), $nbt);
				$entity->spawnToAll();
				$player->sendMessage("§aOluşturuldu!");
			}
		};
		NPCCommand::$npcExtraArg["ft"] = function (SGPlayer $player, array $args) : void{
			static $classes = [
				'kill' => TopKillsFloatingText::class,
				'faction' => FactionFloatText::class,
				'money' => TopMoneyFloatText::class
			];
			(EntityFactory::create($classes[$args[1] ?? 'money'] ?? $classes['money'], $player->getWorld(), EntityFactory::createBaseNBT($player->getPosition())))->spawnToAll();
			$player->sendMessage('Oluşturuldu!');
		};

		VoteForm::$reward .= "\n- " . Utils::addMonetaryUnit(SGPlayer::VOTE_REWARD_MONEY);
	}

	private function initBroadcast() : void{
		BroadcastManager::addBroadcastMessage('tutorial', [TextFormat::GOLD . '/tutorial' . TextFormat::GRAY]);
		BroadcastManager::addBroadcastMessage('warp', [TextFormat::GREEN . '/w' . TextFormat::GRAY]);
		//BroadcastManager::addBroadcastMessage('sponsor', [TextFormat::AQUA . "ServerGates" . TextFormat::GRAY, TextFormat::AQUA . "www.servergates.com" . TextFormat::GRAY]);
	}

	public function updateServerName() : void{
		if($this->getServer()->hasWhitelist()){
			$status = TextFormat::YELLOW . 'BAKIMDA';
		}elseif(TextUtils::inText('BETA', $this->getDescription()->getVersion())){
			$status = TextFormat::RED . 'BETA';
		}else{
			$status = TextFormat::RESET . TextFormat::DARK_GRAY . 'v' . TextFormat::GRAY . $this->getDescription()->getVersion();
		}
		$this->getServer()->getNetwork()->setName(SGCore::SERVER_NAME_FORMAT . TextFormat::GREEN . 'SkyBlock ' . TextFormat::BOLD . $status . TextFormat::RESET);
	}
}