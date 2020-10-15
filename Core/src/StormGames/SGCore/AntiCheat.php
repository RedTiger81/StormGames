<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;

class AntiCheat{
	public const ANTI_INSTA_BREAK = 0;

	/** @var float[] */
	private static $breakTimes = [];

	private static function hash(SGPlayer $player) : int{
		return $player->getId();
	}

	public static function removeFromAntiCheats(SGPlayer $player) : void{
		unset(self::$breakTimes[self::hash($player)]);
	}

	public static function updateBreakTime(SGPlayer $player) : void{
		if(!self::isDisabled(self::ANTI_INSTA_BREAK)){
			self::$breakTimes[self::hash($player)] = floor(microtime(true) * 20);
		}
	}

	public static function checkInstaBreak(BlockBreakEvent $event) : void{
		if(!self::isDisabled(self::ANTI_INSTA_BREAK) and !$event->getInstaBreak()){
			/** @var SGPlayer $player */
			$player = $event->getPlayer();
			$id = self::hash($player);
			if(isset(self::$breakTimes[$id])){
				$expectedTime = ceil($event->getBlock()->getBreakInfo()->getBreakTime($event->getItem()) * 20);
				$expectedTime *= 1 - (0.2 * self::getEffectLevel($player, VanillaEffects::HASTE()));
				$expectedTime *= 1 - (0.2 * self::getEffectLevel($player, VanillaEffects::MINING_FATIGUE()));
				$expectedTime -= 1; //1 tick compensation
				$actualTime = ceil(microtime(true) * 20) - self::$breakTimes[$id];
				if($actualTime < $expectedTime){
					SGCore::getAPI()->getLogger()->debug('§7' . $player->getName() . ' tried to break a block too fast, expected ' . $expectedTime . 'ticks, got ' . $actualTime . ' ticks');
					$event->setCancelled();
				}else{
					unset(self::$breakTimes[$id]);
				}
			}else{
				$event->setCancelled();
			}
		}
	}

	private static function getEffectLevel(SGPlayer $player, Effect $effect) : int{
		$effect = $player->getEffects()->get($effect);
		return ($effect !== null) ? $effect->getEffectLevel() : 0;
	}

	/** @var array */
	private static $disableAC = [];

	public static function disableAntiCheat(int $id) : void{
		self::$disableAC[$id] = true;
	}

	public static function enableAntiCheat(int $id) : void{
		unset(self::$disableAC[$id]);
	}

	public static function isDisabled(int $id) : bool{
		return isset(self::$disableAC[$id]);
	}
}