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
use pocketmine\item\GoldenApple;
use pocketmine\item\GoldenAppleEnchanted;
use pocketmine\item\Item;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\utils\IconUtils;

class MissionAppleHead extends Mission implements MissionEat{
	protected static $id = self::APPLE_HEAD;

	private const TARGET_EAT = 5;

	/** @var int */
	private $eatCount = 0;

	public static function getFormIcon() : FormIcon{
		return IconUtils::get('mission/apple-head');
	}

	public static function getTranslateKey() : string{
		return 'mission.apple.head';
	}

	public static function getMoney() : int{
		return 2000;
	}

	public function getProgressText() : string{
		return Utils::createProgress($this->eatCount, self::TARGET_EAT, self::TARGET_EAT);
	}

	public function eat(Item $item) : void{
		if($item instanceof GoldenApple or $item instanceof GoldenAppleEnchanted){
			$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, ++$this->eatCount, self::TARGET_EAT));
			if($this->eatCount >= self::TARGET_EAT){
				$this->finishMission();
			}
		}
	}
}