<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Pet;

use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class Pig extends Pet{
    public const NETWORK_ID = EntityLegacyIds::PIG;

    /** @var float */
    public $width = 0.9, $height = 0.9;
}