<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\utils\TextFormat;
use StormGames\SGCore\Top;
use StormGames\SGCore\entity\FloatingText;

class TopKillsFloatingText extends FloatingText{
	public const TITLE = "§f--- §cEn iyi katiller §f---\n";
	private const FORMAT = '%s. ' . TextFormat::YELLOW . '%s ' . TextFormat::AQUA . '%s ' . TextFormat::YELLOW . 'öldürme' . TextFormat::EOL;
	private const COLORS = [TextFormat::GREEN, TextFormat::AQUA, TextFormat::RED];

	/** @var int */
	public static $minKills = -1;
	/** @var bool */
	public static $needUpdate = true;

	public static function checkForUpdate(int $kill) : void{
		if(!self::$needUpdate and $kill < self::$minKills){
			self::$needUpdate = true;
		}
	}

	public function update() : void{
		$list = Top::kills();
		self::$minKills = end($list)[1];

		$nameTag = '';
		foreach($list as $key => $value){
			$nameTag .= sprintf(self::FORMAT, (self::COLORS[$key] ?? TextFormat::GOLD) . ($key + 1), $value[0], $value[1]);
		}
		$this->setNameTag(self::TITLE . $nameTag);

		self::$needUpdate = false;
	}

	public function onUpdate(int $currentTick) : bool{
		$hasUpdate = parent::onUpdate($currentTick);

		if($hasUpdate and self::$needUpdate){
			$this->update();
		}

		return $hasUpdate;
	}
}