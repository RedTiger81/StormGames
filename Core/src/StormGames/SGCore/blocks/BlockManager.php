<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\BlockIdentifier as BID;

class BlockManager{
	public static function init() : void{
		BlockFactory::register(
		    new NoteBlock(
		        new BID(BlockLegacyIds::NOTE_BLOCK, 0, null, \StormGames\SGCore\tiles\NoteBlock::class),
                "NoteBlock",
                new BlockBreakInfo(0.8, BlockToolType::AXE)), true);
		BlockFactory::register(new HopperBlock(), true);
		BlockFactory::register(new BeaconBlock(), true);
	}
}