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

use jasonwynn10\VanillaEntityAI\entity\hostile\Ghast;
use jasonwynn10\VanillaEntityAI\entity\hostile\ZombiePigman;
use pocketmine\block\BlockLegacyIds;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class NetherIsland extends IslandBase{
	public function getCobblestoneId() : int{
		return BlockLegacyIds::NETHERRACK;
	}

	public function doChestContents(ChestInventory $inventory) : void{
		$inventory->setContents([
			ItemFactory::get(ItemIds::LAVA), ItemFactory::get(ItemIds::WATER), ItemFactory::get(ItemIds::SPAWN_EGG, ZombiePigman::NETWORK_ID),
			ItemFactory::get(ItemIds::TOTEM), ItemFactory::get(ItemIds::TOTEM), ItemFactory::get(ItemIds::SPAWN_EGG, Ghast::NETWORK_ID),
			ItemFactory::get(ItemIds::NETHER_BRICK, 0, 64), ItemFactory::get(ItemIds::FLINT_AND_STEEL)
		]);
	}
}