<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use StormGames\SGCore\SGPlayer;

class AntiKnockback extends CustomEnchantment{
	public function onDamage(SGPlayer $player, EntityDamageByEntityEvent $event, int $level) : void{
		$event->setKnockBack($event->getKnockBack() - ($level / 10));
		if($event->getKnockBack() < 0){
			$event->setKnockBack(0);
		}
	}
}