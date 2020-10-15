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
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\Top;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\entity\FloatingText;

class TopMoneyFloatText extends FloatingText{
	public const TITLE = "§f--- §aEn zenginler §f---\n";
	private const FORMAT = '%s. ' . TextFormat::YELLOW . '%s ' . TextFormat::AQUA . '%s' . TextFormat::EOL;
	private const COLORS = [TextFormat::GREEN, TextFormat::AQUA, TextFormat::RED];

	/** @var int */
	public static $minMoney = -1;
	/** @var bool */
	public static $needUpdate = true;
	/** @var array */
	public static $list = [];

	public static function checkForUpdate(int $money) : void{
		if(!self::$needUpdate and $money < self::$minMoney){
			self::$needUpdate = true;
		}

		MoneyStatue::checkForUpdate($money);
	}

	public function update() : void{
		self::$list = Top::money();
		self::$minMoney = Utils::removeMonetaryUnit(end(self::$list)[1]);

		$nameTag = '';
		foreach(self::$list as $key => $nameAndMoney){
			$nameTag .= sprintf(self::FORMAT, (self::COLORS[$key] ?? TextFormat::GOLD) . ($key + 1), $nameAndMoney[0], $nameAndMoney[1]);
		}
		$this->setNameTag(self::TITLE . $nameTag);

		MoneyStatue::checkForUpdate(SGPlayer::MONEY_LIMIT); // HACK
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