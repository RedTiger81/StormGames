<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

final class EnchantManager{
	private const FORMAT_LORE_ENCHANTMENT = TextFormat::RESET . TextFormat::GRAY . '%s %s';
	/** @var int[][] */
	private static $priceList = [];

	public static function init() : void{
		self::registerEnchantments();
		self::loadPrices();
	}
	
	private static function addPrice(int $id, int $money, int $xp) : void{
		self::$priceList[$id] = [$money, $xp];
	}
	
	public static function getPrice(int $id) : array{
		return self::$priceList[$id];
	}
	
	private static function registerEnchantments() : void{
		Enchantment::register(new Blind(CustomEnchantment::BLIND, 'enchantment.blind', Enchantment::RARITY_COMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 3));
		Enchantment::register(new Deathbringer(CustomEnchantment::DEATHBRINGER, 'enchantment.deathbringer', Enchantment::RARITY_RARE, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 1));
		Enchantment::register(new IceAspect(CustomEnchantment::ICE_ASPECT, 'enchantment.ice_aspect', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 3));
		Enchantment::register(new Lifesteal(CustomEnchantment::LIFESTEAL, 'enchantment.lifesteal', Enchantment::RARITY_COMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 5));
		Enchantment::register(new Poison(CustomEnchantment::POISON,'enchantment.poison', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 1));
		Enchantment::register(new Vampire(CustomEnchantment::VAMPIRE, 'enchantment.vampire', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 1));
		Enchantment::register(new Furnace(CustomEnchantment::FURNACE, 'enchantment.furnace', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_DIG, Enchantment::SLOT_NONE, 1));
		Enchantment::register(new Energizing(CustomEnchantment::ENERGIZING, 'enchantment.energizing', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_DIG, Enchantment::SLOT_NONE, 4));
		Enchantment::register(new Overload(CustomEnchantment::OVERLOAD, 'enchantment.overload', Enchantment::RARITY_MYTHIC, Enchantment::SLOT_ARMOR, Enchantment::SLOT_NONE, 2));
		Enchantment::register(new AntiKnockback(CustomEnchantment::ANTI_KNOCKBACK, 'enchantment.anti_knockback', Enchantment::RARITY_RARE, Enchantment::SLOT_ARMOR, Enchantment::SLOT_NONE, 2));
		Enchantment::register(new DragonPlus(CustomEnchantment::DRAGON_PLUS, 'enchantment.dragon_plus', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_DIG, Enchantment::SLOT_NONE, 1));

	}

	private static function loadPrices() : void{
		self::addPrice(Enchantment::PROTECTION, 10000, 10);
		self::addPrice(Enchantment::FIRE_PROTECTION, 15000, 15);
		self::addPrice(Enchantment::FEATHER_FALLING, 15000, 10);
		self::addPrice(Enchantment::BLAST_PROTECTION, 15000, 10);
		self::addPrice(Enchantment::PROJECTILE_PROTECTION, 15000, 15);
		self::addPrice(Enchantment::THORNS, 50000, 20);
		self::addPrice(Enchantment::RESPIRATION, 25000, 15);
		self::addPrice(Enchantment::DEPTH_STRIDER, 25000, 50);
		self::addPrice(Enchantment::AQUA_AFFINITY, 5000, 5);
		self::addPrice(Enchantment::SHARPNESS, 15000, 10);
		self::addPrice(Enchantment::SMITE, 15000, 10);
		self::addPrice(Enchantment::BANE_OF_ARTHROPODS, 2500, 10);
		self::addPrice(Enchantment::KNOCKBACK, 15000, 15);
		self::addPrice(Enchantment::FIRE_ASPECT, 50000, 25);
		self::addPrice(Enchantment::LOOTING, 15000, 10);
		self::addPrice(Enchantment::EFFICIENCY, 10000, 10);
		self::addPrice(Enchantment::SILK_TOUCH, 17500, 15);
		self::addPrice(Enchantment::UNBREAKING, 10000, 10);
		self::addPrice(Enchantment::FORTUNE, 10000, 10);
		self::addPrice(Enchantment::POWER, 25000, 25);
		self::addPrice(Enchantment::PUNCH, 25000, 25);
		self::addPrice(Enchantment::FLAME, 25000, 25);
		self::addPrice(Enchantment::INFINITY, 125000, 60);
		self::addPrice(Enchantment::LUCK_OF_THE_SEA, 10000, 20);
		self::addPrice(Enchantment::LURE, 10000, 15);
		self::addPrice(Enchantment::FROST_WALKER, 50000, 20);
		self::addPrice(Enchantment::MENDING, 25000, 15);
		self::addPrice(Enchantment::IMPALING, 10000, 15);
		self::addPrice(Enchantment::RIPTIDE, 10000, 15);
		self::addPrice(Enchantment::LOYALTY, 10000, 15);
		self::addPrice(Enchantment::CHANNELING, 10000, 25);
		self::addPrice(Enchantment::VANISHING, 10000, 30);

		self::addPrice(CustomEnchantment::LIFESTEAL, 200000, 20);
		self::addPrice(CustomEnchantment::BLIND, 150000, 20);
		self::addPrice(CustomEnchantment::DEATHBRINGER, 250000, 20);
		self::addPrice(CustomEnchantment::POISON, 125000, 25);
		self::addPrice(CustomEnchantment::VAMPIRE, 150000, 30);
		self::addPrice(CustomEnchantment::ICE_ASPECT, 150000, 10);
		self::addPrice(CustomEnchantment::FURNACE, 100000, 20);
		self::addPrice(CustomEnchantment::ENERGIZING, 50000, 30);
		self::addPrice(CustomEnchantment::ANTI_KNOCKBACK, 200000, 30);
		self::addPrice(CustomEnchantment::OVERLOAD, 150000, 30);
	}

	public static function addLoreForCustomEnchantment(SGPlayer $player, Item $item = null) : Item{
		if($item === null){
			$item = $player->getInventory()->getItemInHand();
		}

		foreach($item->getEnchantments() as $enchantment){
			if($enchantment->getId() > CustomEnchantment::START_INDEX){
				$lore[] = sprintf(self::FORMAT_LORE_ENCHANTMENT, $player->translate($enchantment->getType()->getName()), Utils::convertRoman($enchantment->getLevel()));
			}
		}

		if(!empty($lore)){
			array_unshift($lore, TextFormat::RESET . TextFormat::LIGHT_PURPLE . $player->translate('enchantment.custom'));
			$item->setLore($lore);
		}

		return $item;
	}

	public static function add(SGPlayer $player, Item $item, EnchantmentInstance $enchantment) : bool{
		$item->addEnchantment($enchantment);
		if($enchantment->getType() instanceof CustomEnchantment){
			self::addLoreForCustomEnchantment($player, $item);
			return true;
		}

		return false;
	}
}