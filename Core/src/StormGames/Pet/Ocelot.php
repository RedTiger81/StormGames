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

class Ocelot extends Pet{
    public const NETWORK_ID = EntityLegacyIds::OCELOT;

    /** @var float */
    public $width = 0.6, $height = 0.7;

    protected function getSpeed() : float{
        return 1.8;
    }
}