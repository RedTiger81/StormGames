<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity\projectile;

use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\Explosion;

class SmallFireball extends Throwable{
	public const NETWORK_ID = EntityLegacyIds::SMALL_FIREBALL;

	protected function onHit(ProjectileHitEvent $event) : void{
		$explosion = new Explosion($this->getLocation(), 2, $this->getOwningEntity());
		$explosion->explodeB();
	}
}