<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Server;
use StormGames\SGCore\manager\ForestWorld;
use StormGames\SGCore\manager\WorldManager;
use function array_flip;

class Furnace extends CustomEnchantment{
	private static $DENIED = [
		BlockLegacyIds::DIAMOND_ORE,
		BlockLegacyIds::REDSTONE_ORE,
		BlockLegacyIds::EMERALD_ORE,
		BlockLegacyIds::STONE
	];

    public function __construct(int $id, string $name, int $rarity, int $primaryItemFlags, int $secondaryItemFlags, int $maxLevel){
        self::$DENIED = array_flip(self::$DENIED);
        parent::__construct($id, $name, $rarity, $primaryItemFlags, $secondaryItemFlags, $maxLevel);
    }

    public function blockBreak(BlockBreakEvent $event, int $level) : void{
    	self::_run($event);
    }

    public static function _run(BlockBreakEvent $event, bool $force = true){
    	$block = $event->getBlock();
	    if(!isset(self::$DENIED[$block->getId()]) and $force){
		    $smelt = Server::getInstance()->getCraftingManager()->matchFurnaceRecipe($block->asItem());
		    if($smelt !== null){
			    $event->setDrops([$smelt->getResult()]);
		    }
	    }
    }
}