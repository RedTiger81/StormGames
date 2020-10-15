<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 28 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\island\island;
 
use jasonwynn10\VanillaEntityAI\entity\passive\Cow;
use jasonwynn10\VanillaEntityAI\entity\passive\Sheep;
use pocketmine\block\BlockLegacyIds;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class NormalIsland extends IslandBase{
	public function getCobblestoneId() : int{
		return BlockLegacyIds::COBBLESTONE;
	}

	public function doChestContents(ChestInventory $inventory) : void{
		$inventory->setContents([
			ItemFactory::get(ItemIds::IRON_PICKAXE)->setCustomName('§l§o§bStorm§fGames')->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 1)),
			ItemFactory::get(ItemIds::IRON_AXE)->setCustomName('§l§o§bStorm§fGames')->addEnchantment(new EnchantmentInstance(Enchantment::UNBREAKING(), 1)),
			ItemFactory::get(ItemIds::IRON_HOE)->setCustomName('§l§o§bStorm§fGames')->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 1)),
			ItemFactory::get(ItemIds::WHEAT_SEEDS, 0, 8), ItemFactory::get(ItemIds::CACTUS,0, 8), ItemFactory::get(ItemIds::SUGARCANE),
			ItemFactory::get(ItemIds::SAND, 0, 6), ItemFactory::get(ItemIds::BUCKET, 10), ItemFactory::get(ItemIds::BUCKET, 8),
			ItemFactory::get(ItemIds::SPAWN_EGG, Sheep::NETWORK_ID, 2), ItemFactory::get(ItemIds::SPAWN_EGG, Cow::NETWORK_ID), ItemFactory::get(ItemIds::TOTEM)
		]);
	}
}