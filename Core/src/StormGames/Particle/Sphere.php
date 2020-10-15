<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle;

use pocketmine\math\Vector3;

abstract class Sphere extends Particle{

	public function createSphere(\pocketmine\world\particle\Particle $particle, int $particleCount = 50, float $radius = 3){
		for($i = 0; $i < $particleCount; ++$i){
			$this->pos->getWorld()->addParticle($this->pos->add(self::getRandomVector()->multiply($radius)), $particle);
		}
	}

	private static function getRandomVector() : Vector3{
		$pos = new Vector3(lcg_value() * 2 - 1, lcg_value() * 2 - 1, lcg_value() * 2 - 1);
		if(($len = $pos->lengthSquared()) > 0){
			$sqr = sqrt($len);
			$pos->setComponents($pos->x / $sqr, $pos->y / $sqr, $pos->z / $sqr);
		}else{
			$pos->setComponents(0, 0, 0);
		}

		return $pos;
	}

}