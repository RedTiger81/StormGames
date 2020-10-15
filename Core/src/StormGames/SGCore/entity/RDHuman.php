<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\entity\Human;

class RDHuman extends Human{

	protected $hitAnimation = true;

	/**
	 * @param bool $hitAnimation
	 */
	public function setHitAnimation(bool $hitAnimation) : void{
		$this->hitAnimation = $hitAnimation;
	}

	protected function doHitAnimation() : void{
		if($this->hitAnimation) parent::doHitAnimation();
	}

	public function hasMovementUpdate() : bool{
        return false;
    }

}