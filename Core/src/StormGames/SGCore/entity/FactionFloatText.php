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

class FactionFloatText extends FloatingText{
	public const TITLE = "§f--- §aEn güçlü klanlar §f---\n";
	private const FORMAT = '%s. ' . TextFormat::YELLOW . '%s ' . TextFormat::AQUA . '%d PW' . TextFormat::EOL;
	private const COLORS = [TextFormat::GREEN, TextFormat::AQUA, TextFormat::RED];

	/** @var int */
	public static $minPower = -1;
	/** @var bool */
	public static $needUpdate = true;

	public static function checkForUpdate(int $power) : void{
		if(!self::$needUpdate and $power < self::$minPower){
			self::$needUpdate = true;
		}
	}

	public function update() : void{
		$powerful = Top::factions();
		self::$minPower = end($powerful)->getPower();

		$nameTag = '';
		foreach($powerful as $key => $faction){
			$nameTag .= sprintf(self::FORMAT, (self::COLORS[$key] ?? TextFormat::GOLD) . ($key + 1), $faction->getName(), $faction->getPower());
		}
		$this->setNameTag(self::TITLE . $nameTag);
	}

	public function onUpdate(int $currentTick) : bool{
		$hasUpdate = parent::onUpdate($currentTick);

		if($hasUpdate and self::$needUpdate){
			$this->update();
			self::$needUpdate = false;
		}

		return $hasUpdate;
	}
}