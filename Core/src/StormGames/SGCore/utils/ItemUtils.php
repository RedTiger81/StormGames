<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;

class ItemUtils{
	
	public const ARMOR_TO_FLAG = [
		ItemIds::LEATHER_HELMET => Enchantment::SLOT_HEAD,
		ItemIds::CHAIN_HELMET => Enchantment::SLOT_HEAD,
		ItemIds::IRON_HELMET => Enchantment::SLOT_HEAD,
		ItemIds::DIAMOND_HELMET => Enchantment::SLOT_HEAD,
		ItemIds::GOLDEN_HELMET => Enchantment::SLOT_HEAD,

		ItemIds::LEATHER_TUNIC => Enchantment::SLOT_TORSO,
		ItemIds::CHAIN_CHESTPLATE => Enchantment::SLOT_TORSO,
		ItemIds::IRON_CHESTPLATE => Enchantment::SLOT_TORSO,
		ItemIds::DIAMOND_CHESTPLATE => Enchantment::SLOT_TORSO,
		ItemIds::GOLDEN_CHESTPLATE => Enchantment::SLOT_TORSO,

		ItemIds::LEATHER_LEGGINGS => Enchantment::SLOT_LEGS,
		ItemIds::CHAIN_LEGGINGS => Enchantment::SLOT_LEGS,
		ItemIds::IRON_LEGGINGS => Enchantment::SLOT_LEGS,
		ItemIds::DIAMOND_LEGGINGS => Enchantment::SLOT_LEGS,
		ItemIds::GOLDEN_LEGGINGS => Enchantment::SLOT_LEGS,

		ItemIds::LEATHER_BOOTS => Enchantment::SLOT_FEET,
		ItemIds::CHAIN_BOOTS => Enchantment::SLOT_FEET,
		ItemIds::IRON_BOOTS => Enchantment::SLOT_FEET,
		ItemIds::DIAMOND_BOOTS => Enchantment::SLOT_FEET,
		ItemIds::GOLDEN_BOOTS => Enchantment::SLOT_FEET
	];
	
	public const CLASS_TO_FLAG = [
		\pocketmine\item\Sword::class => Enchantment::SLOT_SWORD,
		\pocketmine\item\Axe::class => Enchantment::SLOT_AXE,
		\pocketmine\item\Bow::class => Enchantment::SLOT_BOW,
		\pocketmine\item\Pickaxe::class => Enchantment::SLOT_PICKAXE,
		\pocketmine\item\Shovel::class => Enchantment::SLOT_SHOVEL,
		\pocketmine\item\Hoe::class => Enchantment::SLOT_HOE,
		\pocketmine\item\Shears::class => Enchantment::SLOT_SHEARS,
		\pocketmine\item\FlintSteel::class => Enchantment::SLOT_FLINT_AND_STEEL
	];

	public static function findFlag(Item $item) : ?int{
		return ($item instanceof \pocketmine\item\Armor) ? self::ARMOR_TO_FLAG[$item->getId()] ?? null : self::CLASS_TO_FLAG[get_class($item)] ?? null;
	}

	/**
	 * @param Item $item
	 * @return Enchantment[]
	 */
	public static function availableEnchantments(Item $item) : array{
		$flag = self::findFlag($item);
		if($flag !== null){
			$enchantments = [];
			$callable = function() use(&$enchantments, $flag){
				/** @var Enchantment $enchantment */
				/* @noinspection PhpUndefinedFieldInspection */
				foreach(self::$enchantments as $enchantment){
					if($enchantment !== null and ($enchantment->hasPrimaryItemType($flag) or $enchantment->hasSecondaryItemType($flag))){
						$enchantments[] = $enchantment;
					}
				}
			};
			$callable->call(Enchantment::PROTECTION()); /// HACK!

			return $enchantments;
		}

		return [];
	}
}