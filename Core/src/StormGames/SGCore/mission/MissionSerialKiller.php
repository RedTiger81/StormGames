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
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\utils\IconUtils;

class MissionSerialKiller extends Mission implements MissionKill{
	protected static $id = self::SERIAL_KILLER;

	private const TARGET_KILL_COUNT = 8;

	/** @var int */
	private $killCount = 0;

	public static function getFormIcon() : FormIcon{
		return IconUtils::get('mission/serial-killer');
	}

	public static function getTranslateKey() : string{
		return 'mission.serial.killer';
	}

	public static function getMoney() : int{
		return 1000;
	}

	public function getProgressText() : string{
		return Utils::createProgress($this->killCount, self::TARGET_KILL_COUNT, self::TARGET_KILL_COUNT);
	}

	public function kill(SGPlayer $player) : void{
		$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, ++$this->killCount, self::TARGET_KILL_COUNT));
		if($this->killCount >= self::TARGET_KILL_COUNT){
			$this->finishMission();
		}
	}
}