<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Pet;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;

class Sheep extends Pet{
    public const NETWORK_ID = EntityLegacyIds::SHEEP;

    /** @var float */
    public $width = 0.9, $height = 1.3;

    protected function initEntity(CompoundTag $nbt) : void{
        parent::initEntity($nbt);
        $this->setScale(0.5);
	    $this->baby = true;
    }
}