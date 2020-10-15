<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;

class Energizing extends CustomEnchantment{
    public function blockBreak(BlockBreakEvent $event, int $level) : void{
        $player = $event->getPlayer();

        $haste = VanillaEffects::HASTE();
        if(!$player->getEffects()->has($haste)){
            $player->getEffects()->add(new EffectInstance($haste, 20, $level, false));
        }
    }
}