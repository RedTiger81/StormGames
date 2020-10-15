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
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;

class Panda extends Pet{
    public const NETWORK_ID = EntityLegacyIds::PANDA;

    /** @var float */
    public $width = 1.7, $height = 1.5;

    protected function getSpeed() : float{
        return 0.42;
    }

    public static function canUse(SGPlayer $player) : bool{
        return $player->hasPermission(DefaultPermissions::MVP_PLUS);
    }
}