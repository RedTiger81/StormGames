<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\mission;

use pocketmine\form\FormIcon;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

abstract class Mission{
	protected const FORMAT_PROGRESS = TextFormat::DARK_GREEN . '%d' . TextFormat::BOLD . TextFormat::DARK_GRAY . '/' . TextFormat::RESET . TextFormat::GREEN . '%d';

	public const UNKNOWN = -1;
	public const SERIAL_KILLER = 0;
	public const HAWK_EYE = 1;
	public const APPLE_HEAD = 2;
	public const CANNON_BALL = 3;
	public const BUILDER = 4;
	public const MINER = 5;

	private static $missions = [];

	public static function init() : void{
		self::register(MissionSerialKiller::class);
		self::register(MissionHawkEye::class);
		self::register(MissionAppleHead::class);
		self::register(MissionCannonBall::class);
		self::register(MissionBuilder::class);
		self::register(MissionMiner::class);
	}

	/**
	 * @param string|Mission $missionClass
	 */
	public static function register(string $missionClass){
		self::$missions[$missionClass::getId()] = $missionClass;
	}

	/**
	 * @param int $id
	 * @return null|string|Mission
	 */
	public static function getMission(int $id) : ?string{
		return self::$missions[$id] ?? null;
	}

	/**
	 * @return Mission[]
	 */
	public static function getAllMissions() : array{
		return self::$missions;
	}

	/** @var int */
	protected static $id = self::UNKNOWN;

	/** @var SGPlayer */
	protected $player;

	public function __construct(SGPlayer $player){
		$this->player = $player;
	}

	abstract public static function getFormIcon() : FormIcon;

	abstract public static function getTranslateKey() : string;

	abstract public function getProgressText() : string;

	public static function getMoney() : int{
		return 0;
	}

	public static function getRewardText() : string{
		$text = '';
		if(static::getMoney() !== 0){
			$text = Utils::addMonetaryUnit(static::getMoney());
		}

		return $text;
	}

	public static function getName(SGPlayer $player) : string{
		return $player->translate(static::getTranslateKey());
	}

	public static function getDescription(SGPlayer $player) : string{
		return $player->translate(static::getTranslateKey() . '.desc');
	}

	/**
	 * @return int
	 */
	public static function getId(): int{
		return static::$id;
	}

	public function finishMission() : void{
		if(static::getMoney() !== 0){
			$this->player->addMoney(static::getMoney());
		}
		$this->player->completeCurrentMission();
		$this->player->sendTitle(TextFormat::GOLD . $this->player->translate('mission.completed'), TextFormat::WHITE . static::getRewardText());
	}
}