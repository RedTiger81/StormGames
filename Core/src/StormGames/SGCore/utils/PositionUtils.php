<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\entity\Entity;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use pocketmine\entity\Location;
use pocketmine\world\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class PositionUtils{

	/**
	 * Yazıyı pozisyona dönüştürür
	 *
	 * @param string $str
	 * @return null|Position|Vector2|Vector3|Location
	 */
	public static function decodeString(string $str){
		$array = explode(":", $str, 6);

		$count = count($array);
		switch($count){
			case 6: // x:y:z:yaw:pitch:level name
				$level = Utils::getWorldByName(array_pop($array));
				$array = array_map([TextUtils::class, "toNumber"], $array);
				$array[] = $level;
				return new Location(...$array);
				break;
			case 5: // x:y:z:yaw:pitch
				$array = array_map([TextUtils::class, "toNumber"], $array);
				return new Location(...$array);
			case 4: // x:y:z:level name
				$level = Utils::getWorldByName(array_pop($array));
				$array = array_map([TextUtils::class, "toNumber"], $array);
				return Position::fromObject(new Vector3(...$array), $level);
			case 3:
				$array = array_map([TextUtils::class, "toNumber"], $array);
				return new Vector3(...$array);
			case 2:
				$array = array_map([TextUtils::class, "toNumber"], $array);
				return new Vector2(...$array);
			default:
				return null;
		}
	}

	public static function encodeVector3(Vector3 $pos) : string{
		return $pos->x . ":" . $pos->y . ":" . $pos->z;
	}

	public static function encodeVector2(Vector2 $pos) : string{
		return $pos->x . ":" . $pos->y;
	}

	public static function encodePosition(Position $pos) : string{
		return $pos->x . ":" . $pos->y . ":" . $pos->z . ":" . $pos->getWorld()->getFolderName();
	}

	public static function encodeLocation(Location $pos, bool $withLevel = true) : string{
		$level = $withLevel ? ":" . $pos->getWorld()->getFolderName() : "";
		return $pos->x . ":" . $pos->y . ":" . $pos->z . ":" . $pos->yaw . ":" . $pos->pitch . $level;
	}

	public static function encodeLocationFloor(Location $pos, bool $withLevel = true) : string{
		$level = $withLevel ? ":" . $pos->getWorld()->getFolderName() : "";
		return $pos->getFloorX() . ":" . $pos->getFloorY() . ":" . $pos->getFloorZ() . ":" . round($pos->yaw, 3) . ":" . round($pos->pitch, 3) . $level;
	}

	/**
	 * @param World         $level
	 * @param AxisAlignedBB $bb
	 * @param int           $limit
	 * @param string        $class
	 * @param Entity|null   $entity
	 *
	 * @return Entity[]
	 */
	public static function getNearbyEntities(World $level, AxisAlignedBB $bb, int $limit = 0, string $class = Entity::class, Entity $entity = null){
		if($limit < 0){
			return [];
		}

		$nearby = [];

		$minX = ((int) floor($bb->minX - 2)) >> 4;
		$maxX = ((int) floor($bb->maxX + 2)) >> 4;
		$minZ = ((int) floor($bb->minZ - 2)) >> 4;
		$maxZ = ((int) floor($bb->maxZ + 2)) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				/** @var Chunk $chunk */
				foreach($level->getChunk($x, $z) as $chunk){
					foreach($chunk->getEntities() as $ent){
						if($limit !== 0 and count($nearby) == $limit){
							break;
						}

						if($ent !== $entity and $ent instanceof $class and $ent->boundingBox->intersectsWith($bb)){
							$nearby[] = $ent;
						}
					}
				}
			}
		}

		return $nearby;
	}
}