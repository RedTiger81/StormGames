<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use StormGames\SGCore\entity\utils\Skins;

class CustomModel extends RDHuman{

	/** @var string */
	private $skinName, $skinModel;

	public function __construct(World $level, CompoundTag $nbt){
		$this->setSkin(Skins::getSkin($this->skinName = $nbt->getString('SkinName'), $this->skinModel = ($nbt->getString('SkinModel', Skins::MODEL_PLAYER))));
		parent::__construct($level, $nbt);
	}

	public function saveNBT(): CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setString('SkinName', $this->skinName);
		$nbt->setString('SkinModel', $this->skinModel);

		return $nbt;
	}
}