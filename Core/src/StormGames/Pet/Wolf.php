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

class Wolf extends Pet{
    public const NETWORK_ID = EntityLegacyIds::WOLF;

    /** @var float */
    public $width = 0.6, $height = 0.8;

    protected function getSpeed() : float{
        return 1.6;
    }

    public static function canUse(SGPlayer $player) : bool{
        return $player->hasPermission(DefaultPermissions::MVP_PLUS);
    }
}