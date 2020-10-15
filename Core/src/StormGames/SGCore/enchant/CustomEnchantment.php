<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use StormGames\SGCore\SGPlayer;

class CustomEnchantment extends Enchantment implements Listener{
	public const START_INDEX = 100;
	public const LIFESTEAL = self::START_INDEX + 1;
	public const BLIND = self::START_INDEX + 2;
	public const DEATHBRINGER = self::START_INDEX + 3;
	public const POISON = self::START_INDEX + 4;
	public const VAMPIRE = self::START_INDEX + 5;
	public const ICE_ASPECT = self::START_INDEX + 6;

	// TOOLS
	public const FURNACE = self::START_INDEX + 7;
	public const ENERGIZING = self::START_INDEX + 8;

	// ARMOR
	public const OVERLOAD = self::START_INDEX + 9;
	public const ANTI_KNOCKBACK = self::START_INDEX + 10;

	public const DRAGON_PLUS = self::START_INDEX + 11;

	public static function runFunctionForArmor(ArmorInventory $inventory, string $function, ...$args) : void{
		foreach($inventory->getContents() as $item){
			self::runFunction($item, $function, ...$args);
		}
	}

	public static function runFunction(Item $item, string $function, ...$args) : void{
		foreach($item->getEnchantments() as $enchantment){
			if($enchantment->getId() > self::START_INDEX){
				$enchantment->getType()->{$function}(...($args + [1000 => $enchantment->getLevel()]));
			}
		}
	}

	public function putOn(SGPlayer $player, int $level) : void{}

	public function takeOff(SGPlayer $player, int $level) : void{}

	public function onDamage(SGPlayer $player, EntityDamageByEntityEvent $event, int $level) : void{}

	public function attack(SGPlayer $player, Living $entity, EntityDamageEvent $event, int $level) : void{}

	public function blockBreak(BlockBreakEvent $event, int $level) : void{}
}