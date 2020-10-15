<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\form\FormIcon;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use StormGames\SGCore\enchant\CustomEnchantment;

class EnchantmentUtils{
	/** @var Enchantment[] */
	private static $allEnchantments = [];

	private const ARMOR_TO_FLAG = [
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
	
	private const CLASS_TO_FLAG = [
		'pocketmine\item\Sword' => Enchantment::SLOT_SWORD,
		'pocketmine\item\Axe' => Enchantment::SLOT_AXE,
		'pocketmine\item\Bow' => Enchantment::SLOT_BOW,
		'pocketmine\item\Pickaxe' => Enchantment::SLOT_PICKAXE,
		'pocketmine\item\Shovel' => Enchantment::SLOT_SHOVEL,
		'pocketmine\item\Hoe' => Enchantment::SLOT_HOE,
		'pocketmine\item\Shears' => Enchantment::SLOT_SHEARS,
		'pocketmine\item\FlintSteel' => Enchantment::SLOT_FLINT_AND_STEEL
	];

	private static function findFlag(Item $item) : ?int{
		return ($item instanceof \pocketmine\item\Armor) ? self::ARMOR_TO_FLAG[$item->getId()] ?? null : self::CLASS_TO_FLAG[get_class($item)] ?? null;
	}

	/**
	 * @param Item $item
	 * @return Enchantment[]
	 */
	public static function availableEnchantments(Item $item) : array{
		$flag = self::findFlag($item);
		return $flag !== null ? array_values(array_filter(self::getAllEnchantments(), function(Enchantment $enchantment) use($flag): bool{
			return $enchantment->getId() !== CustomEnchantment::DRAGON_PLUS && ($enchantment->hasPrimaryItemType($flag) or $enchantment->hasSecondaryItemType($flag));
		})) : [];
	}

	/**
	 * @return Enchantment[]
	 */
	public static function getAllEnchantments() : array{
		if(empty(self::$allEnchantments)){
			$callable = function() use(&$enchantments){
				/* @noinspection PhpUndefinedFieldInspection */
				$enchantments = array_filter(self::$enchantments, function(?Enchantment $enchantment) : bool{
					return $enchantment !== null;
				});
			};
			$callable->call(Enchantment::PROTECTION()); /// HACK!
			self::$allEnchantments = $enchantments;
		}

		return self::$allEnchantments;
	}

	public static function getIconByRarity(int $rarity) : FormIcon{
		static $rarities = [
			Enchantment::RARITY_COMMON => 'textures/ui/pyramid_level_1',
			Enchantment::RARITY_UNCOMMON => 'textures/ui/pyramid_level_2',
			Enchantment::RARITY_RARE => 'textures/ui/pyramid_level_3',
			Enchantment::RARITY_MYTHIC => 'textures/ui/pyramid_level_4'
		];

		return new FormIcon($rarities[$rarity] ?? 'textures/ui/portalBg', FormIcon::IMAGE_TYPE_PATH);
	}
}