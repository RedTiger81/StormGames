<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use Eren5960\SkyBlock\SkyBlock;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\utils\ExperienceUtils;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\player\GameMode;
use pocketmine\item\ItemFactory;
use pocketmine\permission\PermissionAttachment;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\World;
use StormGames\Rank\Rank;
use StormGames\SGCore\entity\TopMoneyFloatText;
use StormGames\SGCore\mission\Mission;
use StormGames\SGCore\mission\MissionKill;
use StormGames\Rank\RankManager;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\permission\Group;
use StormGames\SGCore\permission\GroupManager;
use StormGames\SGCore\task\SaveSkinHeadTask;
use StormGames\SGCore\task\TutorialTask;
use StormGames\SGCore\utils\MySQL;
use StormGames\SGCore\utils\MySQLValidator;
use StormGames\SGCore\utils\Scoreboard;
use StormGames\SGCore\utils\TextUtils;
use StormGames\SGCore\utils\Utils;

class SGPlayer extends Player{
	public const VISIBLE_ALL = 0;
	public const VISIBLE_STAFFS = 1;
	public const VISIBLE_NONE = 2;
	public const VOTE_REWARD_MONEY = 3000;
	public const SCOREBOARD_MONEY = 5;
	public const SCOREBOARD_COINS = 8;
	public const SCOREBOARD_RANK = 11;
	public const SCOREBOARD_TITLE_PREFIX = TextFormat::DARK_GRAY . '> ' . TextFormat::RESET;
	public const SCOREBOARD_SUBTITLE_PREFIX = TextFormat::GRAY . '> ' . TextFormat::RESET;

	public const MONEY_LIMIT = 5000000;

	public const ERROR_INVALID = 0;
	public const SUCCESS = 1;

	public const MODE_PLAYER = 0;
	public const MODE_TUTORIAL = 1;
	/** @var int */
	private $kills, $deaths;
	/** @var int */
	private $money, $xp;

	/** @var int */
	private $missionDate;
	/** @var Mission|null */
	private $currentMission = null;
	/** @var array */
	private $completedMissions = [];

	/** @var Scoreboard */
	public $scoreboard;

	/** @var Item[] */
	private static $invKit = null, $armorKit = [];

	/** @var int */
	private $mode = self::MODE_PLAYER;

	/** @var bool */
	public $firstLogin = false;

	/** @var int */
	public $kitTime = 0;

	/** @var int */
	public $rankId = -1;

	/** @var Rank */
	public $rank = null;
	/** @var string */
	protected $language = Language::DEFAULT_LANGUAGE;
	/** @var string */
	protected $lastDevice;
	/** @var string */
	protected $biography;
	/** @var int */
	protected $coins;
	/** @var int[] */
	protected $crateKeys;
	/** @var int */
	public $timePlayed, $joinTime;
	/** @var PermissionAttachment */
	protected $attachment;
	/** @var Group */
	protected $group = null;
	/** @var array */
	protected $permissions = [];
	/** @var array */
	protected $levels = [];
	/** @var int */
	protected $currentLevel;

	/** @var int */
	protected $lastChatTime = 0;
	/** @var int */
	protected $visibleStatus = self::VISIBLE_NONE;
	/** @var CosmeticEntry */
	protected $cosmetics;
	/** @var CriminalRecord */
	protected $criminalRecord;
	/** @var MessageEntry */
	public $messages;
	/** @var SGPlayer */
	protected $lastAttacker = null;
	/** @var string */
	public $device = '';

	/** @var string */
	public static $serverName = 'skyblock';

	public $listenMusic = true;

	public $otoSellIds = [];

	public function getLowerCaseName() : string{
		return strtolower($this->username);
	}

	public function sendInfo(): void{
		$title = "§7 **** " . SGCore::SERVER_NAME_FORMAT . " §r§bv" .  $this->server->getPluginManager()->getPlugin("SkyBlock")->getDescription()->getVersion() . " §7**** ";
		$this->sendMessage($title);
		$this->sendMessage("§7» §6" . SGCore::DISCORD . " §r& §6" . SGCore::INSTAGRAM_AND_TWITTER);
	}

	public function loadSGPlayer(MySQL $db) : void{
		$validator = MySQLValidator::get(SGCore::TABLE_PLAYERS);
		$selected = $validator->select($db, 'WHERE username=\'' . $this->getLowerCaseName() . '\'', $this);
		$selected = $validator->translateWithCallable($selected, $this);

		$this->attachment = $this->addAttachment($this->getCore());
		$this->levels = $selected['levels'];
		$this->listenMusic = (bool) $selected["listenMusic"];
		$this->otoSellIds = $selected['sellItemIds'];
		$this->rank = RankManager::getRank((int) $selected["rankId"]);
		$this->setLevelXP($this->levels[self::$serverName] ?? 0);
		$this->setGroup(GroupManager::getGroup($selected['permGroup'])->setTime($selected['groupTime']));
		$this->setDisplayName($this->getNameTag());
		$this->setPermissions($selected['permissions']);
		unset($selected['name'], $selected['username'], $selected['permGroup'], $selected['permissions'], $selected['levels'], $selected["listenMusic"], $selected['sellItemIds']);
		foreach($selected as $key => $value){
			$this->{$key} = $value;
		}
		$this->cosmetics = new CosmeticEntry($this);
		$this->messages = new MessageEntry($this);
		$this->getServer()->getAsyncPool()->submitTask(new SaveSkinHeadTask($this));

		$this->setRemoveFormat(!$this->hasPermission(DefaultPermissions::CHAT_USE_COLORS));

		if($this->firstLogin){
			// GIVE KIT
			$this->getInventory()->setContents(self::$invKit);
			$this->getArmorInventory()->setContents(self::$armorKit);
		}

		$this->setXp((float) $this->xp, false);
		self::checkFlyDay();
		$this->sendInfo();
		$this->loadScoreboard();
		$this->loadMissions($db);
	}

	private function loadMissions(MySQL $db) : void{
		$validator = MySQLValidator::get(SGCore::TABLE_SKYBLOCK_MISSIONS);
		$currentRow = $validator->select($db, 'WHERE username=\'' . $this->getLowerCaseName() . '\'', $this);
		$currentRow = $validator->translateWithCallable($currentRow, $this);

		$this->missionDate = $currentRow['startDate'];
		if($this->checkMissionDate()){
			$class = Mission::getMission($currentRow['currentMissionId']);
			if($class !== null){
				$this->currentMission = new $class($this);
			}
			foreach($currentRow['completedMissions'] as $id){
				$this->completedMissions[$id] = true;
			}
		}
	}

	/** @deprecated  */
	private function loadScoreboard() : void{
		/*$this->scoreboard = new Scoreboard($this, '§bStorm§fGames');
		$sbOptions = [
			'§e@scoreboard.you' => $this->getName(),
			'§a@scoreboard.money' => Utils::addMonetaryUnit($this->getMoney()),
			'§6@scoreboard.coins' => $this->getCoins()
		];
		$i = 0;
		foreach($sbOptions as $text => $value){
			if($i !== 0){
				$this->scoreboard->setLine(++$i, str_repeat('  ', $i + 1));
			}
			$this->scoreboard->setLine(++$i, self::SCOREBOARD_TITLE_PREFIX . $text);
			$this->scoreboard->setLine(++$i, self::SCOREBOARD_SUBTITLE_PREFIX . $value);
		}*/
	}

	public function loadCriminalRecord(MySQL $db) : void{
		$validator = MySQLValidator::get(SGCore::TABLE_CRIMINAL_RECORDS);
		$selected = $validator->select($db, 'WHERE username=\'' . $this->getLowerCaseName() . '\'', $this);
		$selected = $validator->translateWithCallable($selected, $this);

		$banInfo = $selected['banInfo'];

		$this->criminalRecord = new CriminalRecord($this, $banInfo[0] === 1, $banInfo[1], $banInfo[2], $banInfo[3], $selected['banCount'], $selected['kickCount']);
	}

	public static function getDatabase() : MySQL{
		return SGCore::getDatabase();
	}

	public function getLastDevice() : string{
		return $this->lastDevice;
	}

	public function getLanguage() : string{
		return $this->language;
	}

	/**
	 * Dili ayarlar
	 *
	 * @param string $language
	 * @param bool   $update
	 */
	public function setLanguage(string $language, bool $update = true) : void{
		$this->language = Language::getLanguage($language);

		if($update){
			$this->updateDatabase('language', $this->language);
		}
	}

	/**
	 * Cihazını çeker
	 *
	 * @return string
	 */
	public function getDevice() : string{
		return $this->device ?? "unknown";
	}

	/**
	 * Altınları gönderir
	 *
	 * @return int
	 */
	public function getCoins() : int{
		return $this->coins ?? 0;
	}

	/**
	 * @param int  $coins
	 * @param bool $update
	 *
	 * @return bool
	 */
	public function setCoins(int $coins, bool $update = true) : bool{
		if($coins < 0){
			return false;
		}

		$this->coins = $coins;

		if($update){
			$this->updateDatabase('coins', $this->coins);
		}

		$this->loadScoreboard();
		return true;
	}

	/**
	 * @param int  $coins
	 * @param bool $update
	 *
	 * @return bool
	 */
	public function addCoins(int $coins, bool $update = true) : bool{
		if($coins < 0){
			return false;
		}else{
			return $this->setCoins($this->coins + $coins, $update);
		}
	}

	/**
	 * @param int  $coins
	 * @param bool $update
	 *
	 * @return bool
	 */
	public function reduceCoins(int $coins, bool $update = true) : bool{
		if($coins < 0){
			return false;
		}else{
			return $this->setCoins($this->coins - $coins, $update);
		}
	}

	/**
	 * @return string[]
	 */
	public function getPermissions() : array{
		return $this->permissions;
	}

	/**
	 * @param array $permissions
	 */
	public function setPermissions(array $permissions) : void{
		$this->permissions = $permissions;
		$this->attachment->setPermissions($this->permissions);
	}

	/**
	 * @param string[] $permissions
	 */
	public function addPermissions(string ...$permissions) : void{
		foreach($permissions as $permission){
			$negative = substr($permission, 0, 1) == '-';
			$this->permissions[$negative ? $permission : substr($permission, 1)] = !$negative;
		}
		$this->attachment->setPermissions($this->permissions);
	}

	/**
	 * @param string[] $permissions
	 */
	public function removePermissions(string ...$permissions) : void{
		foreach($permissions as $permission){
			unset($this->permissions[$permission]);
		}
		$this->attachment->setPermissions($this->permissions);
	}

	/**
	 * @return Group
	 */
	public function getGroup() : ?Group{
		return $this->group ?? null;
	}

	/**
	 * @param Group $group
	 */
	public function setGroup(Group $group) : void{
		$this->group = $group;
		$this->updateDatabase("permGroup", $group->getName());
		$this->updateDatabase("groupTime", $group->getTime());

		$this->updateNameTag();
		$this->attachment->clearPermissions();
		$this->attachment->setPermissions($group->getPermissions());
	}

	public function getCrateKeys(string $crate) : int{
		return $this->crateKeys[$crate] ?? 0;
	}

	public function setCrateKeys(string $crate, int $keys){
		$this->crateKeys[$crate] = $keys;
	}

	public function addCrateKeys(string $crate, int $keys = 1) : void{
		if(isset($this->crateKeys[$crate])){
			$this->crateKeys[$crate] += $keys;
		}else{
			$this->crateKeys[$crate] = $keys;
		}
	}

	public function subtractCrateKeys(string $crate, int $keys = 1) : void{
		if(isset($this->crateKeys[$crate])) $this->crateKeys[$crate] -= $keys;
	}

	public function giveVoteReward() : void{
		$this->addCrateKeys('vote', 3);
		$this->addMoney(self::VOTE_REWARD_MONEY);
	}

	/**
	 * Çevirilmiş yazıyı döndürür
	 *
	 * @param string $message
	 * @param array  $parameters
	 *
	 * @return string
	 */
	public function translate(string $message, array $parameters = []) : string{
		return Language::translate($this->getLanguage(), $message, $parameters);
	}

	public function translateExtended(string $message, array $args = [], string $separator = '%') : string{
		return Language::translateExtended($this->getLanguage(), $message, $args, $separator);
	}

	public function getCore() : SGCore{
		return SGCore::getAPI();
	}

	public function getTimePlayedNow() : int{
		return $this->timePlayed + (time() - $this->joinTime);
	}

	public function addTimePlayed(int $add) : void{
		$this->timePlayed += $add;
	}

	/**
	 * @return string
	 */
	public function getBiography() : string{
		return $this->biography;
	}

	/**
	 * @param string $biography
	 * @param bool   $update
	 */
	public function setBiography(string $biography, bool $update = true) : void{
		$this->biography = $biography;

		if($update){
			$this->updateDatabase('biography', $this->biography);
		}
	}

	/**
	 * @return int
	 */
	public function getLastChatTime() : int{
		return $this->lastChatTime ?? 0;
	}

	/**
	 * @param int $lastChatTime
	 */
	public function setLastChatTime(int $lastChatTime) : void{
		$this->lastChatTime = $lastChatTime;
	}

	/**
	 * @param SGPlayer $lastAttacker
	 */
	public function setLastAttacker(?SGPlayer $lastAttacker) : void{
		$this->lastAttacker = $lastAttacker;
	}

	/**
	 * @return SGPlayer
	 */
	public function getLastAttacker() : ?SGPlayer{
		return $this->lastAttacker;
	}

	public function setLevelXP(int $xp) : void{
		if($xp < 0){
			$xp = 0;
		}

		$currentLevel = (int) ExperienceUtils::getLevelFromXp($xp);
		$this->currentLevel = $currentLevel;
		$this->levels[self::$serverName] = $xp;
		if($this->group !== null){
			$this->updateNameTag();
		}
	}

	public function addLevelXP(int $xp) : void{
		static $xxx = [
			"mvp+" => 4,
			"mvp" => 3,
			"vip+" => 2
		];
		$this->setLevelXP($this->getLevelXP() + ($xp * ($xxx[$this->getGroup()->getName()] ?? 1)));
	}

	public function subtractLevelXP(int $xp) : void{
		$this->setLevelXP($this->getLevelXP() - $xp);
	}

	public function getLevelXP() : int{
		return $this->levels[self::$serverName];
	}

	public function getCurrentLevel() : int{
		return $this->currentLevel ?? 0;
	}

	public function setCurrentLevel(int $level) : void{
		$this->setLevelXP(ExperienceUtils::getXpToCompleteLevel($level));
	}

	/**
	 * Çıkarken yapılacak işlemler
	 */
	public function onQuit() : void{
		if($this->cosmetics !== null){
			$this->cosmetics->reset();
			$this->addTimePlayed(time() - $this->joinTime);
			$this->updateAllDatabase();
		}
	}

	/**
	 * İsim etiketini günceller
	 */
	public function updateNameTag() : void{
		$prefix = $this->getGroup()->getFormat();
		if(!empty($prefix)){
			$prefix .= ' ' . TextFormat::RESET;
		}

		$level = SkyBlock::getPlayerIslandLevel($this);
		$this->setNameTag(TextUtils::numberToColor($level) . "Sv. " . $level . TextFormat::RESET . ' ' . $prefix . ($this->rank->getName() === 'empty' ? '' : $this->rank->getNameFor($this)) . TextFormat::RESET . ' ' . $this->getDisplayName());
	}

	/**
	 * @deprecated
	 */
	public function sendJoinItems() : void{
		$this->getInventory()->setContents([
			0 => ItemFactory::get(ItemIds::DYE, 10)->setCustomName(TextFormat::RESET . TextFormat::GREEN . $this->translate('items.lobby.visible', [TextFormat::GRAY . $this->translate('visible.everyone')])),
			4 => ItemFactory::get(ItemIds::SKULL, 3)->setCustomName(TextFormat::RESET . TextFormat::YELLOW . $this->translate('items.lobby.menu')),
			8 => ItemFactory::get(ItemIds::ENDER_CHEST)->setCustomName(TextFormat::RESET . TextFormat::GOLD . $this->translate('items.lobby.shop')),
		]);
	}

	public function reset(bool $updateName = true, bool $clearInventory = true, GameMode $gameMode = null) : void{
		if($clearInventory and $this->inventory !== null and $this->armorInventory !== null){
			$this->inventory->clearAll();
			$this->armorInventory->clearAll();
		}

		$this->setGamemode($gameMode ?? GameMode::ADVENTURE());

		if($updateName) $this->setDisplayName($this->getName());

		$this->setMaxHealth(20);
		$this->setHealth(20);
		$this->cosmetics->reset();
	}

	public function isAvailable() : bool{
		return true;
	}

	/**
	 * @param int $visibleStatus
	 */
	public function setVisibleStatus(int $visibleStatus) : void{
		$this->visibleStatus = $visibleStatus;

		switch($this->visibleStatus){
			case self::VISIBLE_ALL:
				/** @var SGPlayer $player */ foreach($this->getServer()->getOnlinePlayers() as $player){
				if(!$this->canSee($player)){
					$this->showPlayer($player);
				}
			}
				break;
			case self::VISIBLE_STAFFS:
				/** @var SGPlayer $player */ foreach($this->getServer()->getOnlinePlayers() as $player){
				switch($player->getGroup()->getName()){
					case 'owner':
					case 'mod':
					case 'vip':
					case 'vip+':
					case 'mvp':
					case 'mvp+':
						if(!$this->canSee($player)){
							$this->showPlayer($player);
						}
						break;
					default:
						$this->hidePlayer($player);
						break;
				}
			}
				break;
			case self::VISIBLE_NONE:
				foreach($this->getServer()->getOnlinePlayers() as $player){
					$this->hidePlayer($player);
				}
				break;
		}
	}

	/**
	 * @return CosmeticEntry
	 */
	public function getCosmetics() : CosmeticEntry{
		return $this->cosmetics;
	}

	/**
	 * @return CriminalRecord
	 */
	public function getCriminalRecord() : CriminalRecord{
		return $this->criminalRecord;
	}

	/**
	 * Tüm veritabanını günceller
	 */
	public function updateAllDatabase() : void{
		$db = SGCore::getDatabase();

		MySQLValidator::get(SGCore::TABLE_PLAYERS)->update($db, [
			'biography' => $this->biography,
			'language' => $this->language,
			'lastDevice' => $this->getDevice(),
			'crateKeys' => TextUtils::fromArray($this->crateKeys, true),
			'permGroup' => $this->getGroup()->getName(),
			'levels' => TextUtils::fromArray($this->levels, true),
			'groupTime' => $this->getGroup()->getTime(),
			'permissions' => TextUtils::fromArray(array_keys($this->getPermissions())),
			'timePlayed' => $this->timePlayed,
			'coins' => $this->coins,
			'listenMusic' => (int) $this->listenMusic,
			'kills' => $this->kills,
			'deaths' => $this->deaths,
			'money' => $this->money,
			'xp' => (string) $this->xp,
			'kitTime' => $this->kitTime,
			'rankId' => $this->rankId,
			'sellItemIds' => TextUtils::fromArray(array_keys($this->otoSellIds))
		], 'WHERE username=\'' . $this->getLowerCaseName() . '\'');

		MySQLValidator::get(SGCore::TABLE_CRIMINAL_RECORDS)->update($db, [
			'banInfo' => $this->criminalRecord->isBanned() . ':' . $this->criminalRecord->getBanReason() . ':' . $this->criminalRecord->getBanTimestamp() . ':' . $this->criminalRecord->getBannedBy(),
			'banCount' => $this->criminalRecord->getBanCount(),
			'kickCount' => $this->criminalRecord->getKickCount()
		], 'WHERE username=\'' . $this->getLowerCaseName() . '\'');

		MySQLValidator::get(SGCore::TABLE_SKYBLOCK_MISSIONS)->update($db, [
			'currentMissionId' => $this->currentMission !== null ? $this->currentMission::getId() : -1,
			'startDate'=> $this->missionDate,
			'completedMissions' => TextUtils::fromArray(array_keys($this->completedMissions))
		], 'WHERE username=\'' . $this->getLowerCaseName() . '\'');
	}

	/**
	 * Veritabanında sadece bir veri günceller
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param string $tableName
	 */
	public function updateDatabase(string $key, $value, string $tableName = SGCore::TABLE_PLAYERS) : void{
		MySQLValidator::get($tableName)->update(self::getDatabase(), [$key => $value], 'WHERE username=\'' . $this->getLowerCaseName() . '\'');
	}

	# OVERRIDE

	public function setDisplayName(string $name): void{
		parent::setDisplayName($name);
		$this->updateNameTag();
	}

	public function onUpdate(int $currentTick) : bool{
		$hasUpdate = parent::onUpdate($currentTick);

		if($this->cosmetics !== null){
			$this->cosmetics->update($currentTick);
		}

		return $hasUpdate;
	}

	public function inDefaultLevel() : bool{
		return $this->getWorld() !== null and $this->getWorld()->getId() === $this->server->getWorldManager()->getDefaultWorld()->getId();
	}

	public function isVip() : bool{
		return $this->hasPermission(DefaultPermissions::VIP);
	}

	public function isOwner() : bool{
		return $this->hasPermission(DefaultPermissions::ADMIN);
	}

	public function isModerator() : bool{
		return $this->hasPermission(DefaultPermissions::MODERATOR);
	}
	/**
	 * @return int
	 */
	public function getKills() : int{
		return $this->kills;
	}

	/**
	 * @param int $kill
	 * @param bool $update
	 */
	public function setKills(int $kill, bool $update = true) : void{
		$this->kills = $kill;

		if($update){
			$this->updateDatabase("kills", $this->kills);
		}
	}

	/**
	 * @param int $add
	 * @param bool $update
	 */
	public function addKill(int $add = 1, bool $update = true) : void{
		$this->setKills($this->kills + $add, $update);
	}

	/**
	 * @return int
	 */
	public function getDeaths() : int{
		return $this->deaths;
	}

	/**
	 * @param int $death
	 * @param bool $update
	 */
	public function setDeaths(int $death, bool $update = true) : void{
		$this->deaths = $death;

		if($update){
			$this->updateDatabase("deaths", $this->deaths);
		}
	}

	/**
	 * @param int $add
	 * @param bool $update
	 */
	public function addDeath(int $add = 1, bool $update = true) : void{
		$this->setDeaths($this->deaths + $add, $update);
	}

	/**
	 * @return float
	 */
	public function getKD() : float{
		if(($death = $this->getDeaths()) <= 0){
			return $this->getKills();
		}

		return $this->getKills() / $death;
	}

	/**
	 * @return int
	 */
	public function getMoney() : int{
		return $this->money ?? 0;
	}

	/**
	 * @param int $money
	 * @param bool $update
	 * @param bool $force
	 * @return bool
	 */
	public function setMoney(int $money, bool $update = true, bool $force = false) : bool{
		if(!$force and $money > self::MONEY_LIMIT){
			return false;
		}

		if($money < 0){
			return false;
		}

		$this->money = $money;
		TopMoneyFloatText::checkForUpdate($money);

		if($update){
			$this->updateDatabase("money", $this->money);
		}

		if($this->scoreboard !== null){
			$this->scoreboard->setLine(self::SCOREBOARD_MONEY, self::SCOREBOARD_SUBTITLE_PREFIX . Utils::addMonetaryUnit($this->money));
		}


		return true;
	}

	/**
	 * @param int $money
	 * @param bool $update
	 * @return bool
	 */
	public function addMoney(int $money, bool $update = true) : bool{
		if($money <= 0){
			return false;
		}

		return $this->setMoney($this->money + $money, $update);
	}

	/**
	 * @param int $money
	 * @param bool $update
	 * @return bool
	 */
	public function reduceMoney(int $money, bool $update = true) : bool{
		if($money <= 0){
			return false;
		}

		return $this->setMoney($this->money - $money, $update);
	}

	/**
	 * @return float
	 */
	public function getXp() : float{
		return $this->xp;
	}

	/**
	 * @param float $xp
	 * @param bool $update
	 * @return bool
	 */
	public function setXp(float $xp, bool $update = true) : bool{
		if($xp < 0){
			return false;
		}

		$this->xpManager->setXpLevel((int) $xp);
		$this->xp = $xp;
		if($update){
			$this->updateDatabase('xp', (string) $this->xp);
		}

		return true;
	}

	/**
	 * @param float $xp
	 * @param bool $update
	 * @return bool
	 */
	public function addExp(float $xp, bool $update = true) : bool{
		if($xp <= 0){
			return false;
		}

		return $this->setXp($this->xp + $xp, $update);
	}

	/**
	 * @param float $xp
	 * @param bool $update
	 * @return bool
	 */
	public function reduceXp(float $xp, bool $update = true) : bool{
		if($xp <= 0){
			return false;
		}

		return $this->setXp($this->xp - $xp, $update);
	}

	/**
	 * @return bool
	 */
	public function checkMissionDate() : bool{
		if((time() - $this->missionDate) >= 86400){ // 1 day
			$this->setCurrentMission(null);
			$this->missionDate = time();
			$this->completedMissions = [];
			return false;
		}

		return true;
	}

	/**
	 * @return null|Mission
	 */
	public function getCurrentMission() : ?Mission{
		return $this->currentMission;
	}

	/**
	 * @param null|Mission $currentMission
	 */
	public function setCurrentMission(?Mission $currentMission) : void{
		unset($this->currentMission);
		$this->currentMission = $currentMission;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function isMissionCompleted(int $id) : bool{
		return isset($this->completedMissions[$id]);
	}

	public function completeCurrentMission() : void{
		$this->addLevelXP(15);
		$this->completedMissions[$this->currentMission->getId()] = true;
		$this->setCurrentMission(null);
	}

	/**
	 * @return int
	 */
	public function getMode() : int{
		return $this->mode;
	}

	public function setTutorialMode(bool $value) : void{
		$this->setImmobile($value);
		$this->mode = $value ? self::MODE_TUTORIAL : self::MODE_PLAYER;
		if($value){
			SGCore::getAPI()->getScheduler()->scheduleRepeatingTask(new TutorialTask($this), 60); //3 seconds
		}
	}

	public function getRank(): Rank{
		return $this->rank;
	}

	public function getRankId(): int{
		return $this->rankId;
	}

	public function setRank(int $rankId): void {
		$this->rankId = $rankId;
		$this->rank = RankManager::getRank($rankId);
		$this->addPermissions('+' . $this->rank->getPermission());

		$this->updateDatabase('rankId', $rankId);
		$this->updateNameTag();

		if($this->scoreboard !== null){
			$this->scoreboard->setLine(self::SCOREBOARD_RANK, self::SCOREBOARD_SUBTITLE_PREFIX . $this->rank->getName());
		}
	}

	public function onKill() : void{
		$this->effectManager->add(new EffectInstance(VanillaEffects::REGENERATION(), 40, 1));
		$this->addKill();
		$this->addMoney(20, false);
		$this->addLevelXP(2);
		$this->getPosition()->getWorld()->addSound($this->getPosition(), new AnvilUseSound(), [$this]);

		if($this->getCurrentMission() instanceof MissionKill){
			/** @noinspection PhpUndefinedMethodInspection */ // #blameJetbrains
			$this->getCurrentMission()->kill($this);
		}
	}

	public static $tips = null;

	protected function switchWorld(World $targetLevel) : bool{
		$oldLevel = $this->location->getWorld();
		$hasSwitch = parent::switchWorld($targetLevel);

		if(self::$tips === null){
			self::$tips = (new Config(SGCore::getAPI()->getResourcesDir() . 'tips.yml', Config::YAML))->get('tips', []);
		}

		if(Server::getInstance()->isRunning() && $hasSwitch){
			$this->sendMessage("§" . rand(0, 9) . "TIP > §a" . self::$tips[array_rand(self::$tips)]);
			if($oldLevel instanceof World && Utils::getDimension($oldLevel) !== Utils::getDimension($targetLevel)){
				$this->actionDimension($targetLevel);
			}
		}

		return $hasSwitch;
	}

	public static $isSaturday = null;

	private static function checkFlyDay(): void{
		if(self::$isSaturday === null){
			self::$isSaturday = date('D', time()) === 'Sat';
		}
	}

	public function actionDimension(World $world): void{
		$pk = new ChangeDimensionPacket();
		$pk->position = $this->getPosition();
		$pk->dimension = Utils::getDimension($world);
		$this->getNetworkSession()->sendDataPacket($pk);
	}

	public function onDeath() : void{
		if(Utils::getDimension($this->getWorld()) !== DimensionIds::OVERWORLD){ // A temporary fix
			$this->switchWorld($this->getServer()->getWorldManager()->getDefaultWorld());
		}
		parent::onDeath();
	}
}