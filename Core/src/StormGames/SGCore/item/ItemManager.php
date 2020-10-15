<?php

declare(strict_types=1);

namespace StormGames\SGCore\item;

use pocketmine\block\BlockLegacyIds;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class ItemManager{
    public static function init() : void{
        ItemFactory::register(new Firework(), true);
        CreativeInventory::add(new Firework());
     //   ItemFactory::register($item = new ItemBlock(BlockLegacyIds::HOPPER_BLOCK, 0, ItemIds::HOPPER), true);
      //  CreativeInventory::add($item);
    }
}