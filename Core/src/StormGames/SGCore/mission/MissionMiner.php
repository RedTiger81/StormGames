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
use pocketmine\block\BlockLegacyIds;
use pocketmine\form\FormIcon;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\utils\IconUtils;

class MissionMiner extends Mission implements MissionBlock{
	protected static $id = self::MINER;

	private const TARGET_DIAMOND = 1;
	private const TARGET_GOLD = 4;
	private const TARGET_COAL = 10;

	/** @var int */
	private $diamondCount = 0, $goldCount = 0, $coalCount = 0;

	public static function getFormIcon() : FormIcon{
		return IconUtils::get('mission/miner');
	}

	public static function getTranslateKey() : string{
		return 'mission.miner';
	}

	public static function getMoney() : int{
		return 3500;
	}

	public function getProgressText() : string{
		$text = Utils::createProgress($this->diamondCount, self::TARGET_DIAMOND, self::TARGET_DIAMOND, TextFormat::AQUA, TextFormat::DARK_AQUA);
		$text .= Utils::createProgress($this->goldCount, self::TARGET_GOLD, self::TARGET_GOLD, TextFormat::GOLD, TextFormat::YELLOW);
		$text .= Utils::createProgress($this->coalCount, self::TARGET_COAL, self::TARGET_COAL, TextFormat::DARK_GRAY, TextFormat::GRAY);

		return $text;
	}

	public function blockBreak(Block $block) : void{
		switch($block->getId()){
			case BlockLegacyIds::GOLD_ORE:
				++$this->goldCount;
				$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, $this->goldCount, self::TARGET_GOLD));
				break;
			case BlockLegacyIds::COAL_ORE:
				++$this->coalCount;
				$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, $this->coalCount, self::TARGET_COAL));
				break;
			case BlockLegacyIds::DIAMOND_ORE:
				++$this->diamondCount;
				$this->player->sendTip(sprintf(self::FORMAT_PROGRESS, $this->diamondCount, self::TARGET_DIAMOND));
				break;
		}

		if($this->diamondCount >= self::TARGET_DIAMOND and $this->goldCount >= self::TARGET_GOLD and $this->coalCount >= self::TARGET_COAL){
			$this->finishMission();
		}
	}

	public function blockPlace(Block $block) : void{
	}
}