<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Crate;

use pocketmine\block\BlockLegacyIds;
use pocketmine\form\Form;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use StormGames\Crate\CrateContents;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\SGCore\SGPlayer;

class BuilderCrateContents extends CrateContents{

    private const CONTENTS = [
        [ItemIds::QUARTZ_BLOCK],
        [ItemIds::LOG, 3],
        [ItemIds::LOG2, 1],
        [ItemIds::TERRACOTTA, 15],
        [ItemIds::CONCRETE, 15],
        [ItemIds::STONE, 6],
        [ItemIds::COBBLESTONE],
        [ItemIds::PRISMARINE, 3],
        [ItemIds::SANDSTONE, 2],
        [ItemIds::WOOL, 15],
        [ItemIds::RED_SANDSTONE, 2],
        [ItemIds::STAINED_CLAY, 15],
        [ItemIds::STONEBRICK, 2],
        [ItemIds::END_STONE],
        [ItemIds::END_BRICKS],
        [ItemIds::NETHER_BRICK],
        [ItemIds::BRICK_BLOCK],
	    [ItemIds::MAGMA],
	    [ItemIds::SPONGE],
	    [ItemIds::GRASS],
	    [ItemIds::GLASS],
	    [ItemIds::WHEAT_BLOCK],
	    [ItemIds::TERRACOTTA],
	    [ItemIds::COBBLESTONE_WALL],
	    [ItemIds::STONE_WALL],
	    [ItemIds::JUNGLE_WALL_SIGN],
	    [ItemIds::SPRUCE_WALL_SIGN],
	    [ItemIds::WALL_SIGN]
    ];

    /** @var Item[] */
    private static $content = null;

    public function __construct(){
        if(self::$content === null){
            self::$content = [];
            foreach(self::CONTENTS as $item){
                if(isset($item[1])){
                    for($meta=0; $meta<=$item[1]; $meta++){
                        self::$content[] = ItemFactory::get($item[0], $meta);
                    }
                }else{
                    self::$content[] = ItemFactory::get($item[0]);
                }
            }
        }
    }

    public function getName() : string{
        return "builder";
    }

    public function giveRandomContent(SGPlayer $player, string &$contentName) : void{
        $item = clone self::$content[array_rand(self::$content)];
        $item->setCount([32, 48, 64, 128][mt_rand(0, 3)]);
        $player->getInventory()->addItem(EnchantManager::addLoreForCustomEnchantment($player, $item));
        $contentName = "x" . $item->getCount() . " " . $item->getVanillaName();
    }

    public function getPrice() : int{
        return 2500;
    }

    public function getBuyForm(SGPlayer $player) : Form{
        return new BuyCrateKeyForm($player, $this->getName(), $this->getPrice());
    }

	public function getBlockId() : int{
		return BlockLegacyIds::BRICK_BLOCK;
	}
}