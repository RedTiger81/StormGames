<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\tiles;

use pocketmine\block\tile\TileFactory;

class TileManager{
	public static function init() : void{
		TileFactory::register(NoteBlock::class, ['noteblock', 'minecraft:noteblock']);
		TileFactory::register(Hopper::class, ['Hopper', 'minecraft:hopper']);
		TileFactory::register(Beacon::class, ['Beacon', 'minecraft:beacon']);
		TileFactory::override(\pocketmine\block\tile\Hopper::class, Hopper::class);
	}
}