<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity\furniture;

use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use StormGames\SGCore\entity\Furniture;
use StormGames\SGCore\entity\RDHuman;
use StormGames\SGCore\entity\utils\Skins;

class Table extends RDHuman implements Furniture{

	public function __construct(World $level, CompoundTag $nbt){
		$this->skin = Skins::getSkin("normal", Skins::MODEL_TABLE);
		parent::__construct($level, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(1);

		parent::initEntity($nbt);
	}

	protected function doHitAnimation() : void{

	}

	public function hasMovementUpdate() : bool{
		return false;
	}

	public function getDrops() : array{
		return parent::getDrops(); // TODO: Change the autogenerated stub
	}
}