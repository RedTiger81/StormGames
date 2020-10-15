<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\mission;

use pocketmine\block\Block;
use pocketmine\form\FormIcon;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\utils\IconUtils;

class MissionCannonBall extends Mission implements MissionBlock{
	protected static $id = self::CANNON_BALL;

	private const TARGET_BREAK = 50;

	/** @var int */
	private $breakCount = 0;

	public static function getFormIcon() : FormIcon{
		return IconUtils::get('mission/cannonball');
	}

	public static function getTranslateKey() : string{
		return 'mission.cannon.ball';
	}

	public static function getMoney() : int{
		return 1000;
	}

	public function getProgressText() : string{
		return Utils::createProgress($this->breakCount, 10, self::TARGET_BREAK);
	}

	public function blockBreak(Block $block) : void{
		$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, ++$this->breakCount, self::TARGET_BREAK));
		if($this->breakCount >= self::TARGET_BREAK){
			$this->finishMission();
		}
	}

	public function blockPlace(Block $block) : void{
	}
}