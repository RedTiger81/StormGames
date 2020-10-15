<?php

/*
 
 
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */

declare(strict_types=1);

namespace StormGames\SGCore\enchant;

use StormGames\SGCore\SGPlayer;

class Overload extends CustomEnchantment{
	public function putOn(SGPlayer $player, int $level) : void{
		$player->setMaxHealth((int) round($player->getMaxHealth() + $level));
		$player->setHealth($player->getHealth() + $level);
	}

	public function takeOff(SGPlayer $player, int $level) : void{
		$player->setMaxHealth((int) round($player->getMaxHealth() - $level));
		$player->setHealth($player->getHealth() - $level);
	}
}