<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace jasonwynn10\VanillaEntityAI\utils;

use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use pocketmine\math\Vector3;

class RandomPositionGenerator{

	/**
	 * @param CreatureBase     $entity
	 * @param int     $xz
	 * @param int     $y
	 * @param Vector3 $targetPos
	 *
	 * @return Vector3|null
	 */
	public static function findRandomTargetBlockAwayFrom(CreatureBase $entity, int $xz, int $y, Vector3 $targetPos) : ?Vector3{
		return self::findRandomTargetBlock($entity, $xz, $y, $entity->getLocation()->subtract($targetPos));
	}

	public static function findRandomTargetBlock(CreatureBase $entity, int $dxz, int $dy, ?Vector3 $targetPos = null) : ?Vector3{
		$currentWeight = PHP_INT_MIN;
		$currentPos = null;
		for($i = 0; $i < 10; $i++){
			$x = $entity->random->nextBoundedInt(2 * $dxz + 1) - $dxz;
			$y = $entity->random->nextBoundedInt(2 * $dy + 1) - $dy;
			$z = $entity->random->nextBoundedInt(2 * $dxz + 1) - $dxz;

			if($targetPos === null or ($x * $targetPos->x + $z * $targetPos->z) > 0){
				$targetVector = $entity->getLocation()->add($x, $y, $z);

				// TODO: remove this temp fix
				if(($maxY = $entity->getWorld()->getWorldHeight() + 1) < $targetVector->y){
					$targetVector->y = $maxY;
				}

				$weight = 0.0;
				if($weight > $currentWeight){
					$currentWeight = $weight;
					$currentPos = $targetVector;
				}
			}
		}

		return $currentPos;
	}
}