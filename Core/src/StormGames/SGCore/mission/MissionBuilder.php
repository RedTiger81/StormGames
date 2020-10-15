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
use StormGames\SGCore\manager\WorldManager;
use StormGames\SGCore\manager\PlotWorld;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\utils\IconUtils;

class MissionBuilder extends Mission implements MissionBlock{
	protected static $id = self::BUILDER;

	private const TARGET_PLACE = 50;

	/** @var int */
	private $placeCount = 0;

	public static function getFormIcon() : FormIcon{
		return IconUtils::get('mission/builder');
	}

	public static function getTranslateKey() : string{
		return 'mission.builder';
	}

	public static function getMoney() : int{
		return 1000;
	}

	public function getProgressText() : string{
		return Utils::createProgress($this->placeCount, 10, self::TARGET_PLACE);
	}

	public function blockPlace(Block $block) : void{
		$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, ++$this->placeCount, self::TARGET_PLACE));
		if($this->placeCount >= self::TARGET_PLACE){
			$this->finishMission();
		}
	}

	public function blockBreak(Block $block) : void{}
}