<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use StormGames\SGCore\SGPlayer;

class Deathbringer extends CustomEnchantment{
    public function attack(SGPlayer $attacker, Living $entity, EntityDamageEvent $event, int $level) : void{
        if(mt_rand(0, 2) == 0){
	        $event->setBaseDamage($event->getBaseDamage() * 2);
        }
    }
}