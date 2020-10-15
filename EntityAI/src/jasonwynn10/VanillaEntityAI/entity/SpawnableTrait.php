<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\entity\EntityFactory;
use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;

trait SpawnableTrait {
	public $spawnLight = 7; // default to monsters

	/**
	 * @param Position         $spawnPos
	 * @param null|CompoundTag $spawnData
	 *
	 * @param string|null      $class
	 *
	 * @return null|CreatureBase
	 */
	public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null, ?string $class = null) : ?CreatureBase {
		$nbt = EntityFactory::createBaseNBT($spawnPos);
		if(isset($spawnData)) {
			$nbt = $spawnData->merge($nbt);
			$nbt->setInt("id", self::NETWORK_ID);
		}
		/** @var CreatureBase $entity */
		$entity = EntityFactory::create($class ?? self::class, $spawnPos->getWorld(), $nbt);
		// TODO: work on logic here more
		/*$light = $entity->getWorld()->getFullLight($entity->getLocation()->floor());
		if(!$spawnPos->isValid() or count($entity->getBlocksAround()) > 1) {
			$entity->flagForDespawn();
			return null;
		}else {*/
			$entity->spawnToAll();
			return $entity;
	//	}
	}

	/**
	 * @return int
	 */
	public function getSpawnLight() : int {
		return $this->spawnLight;
	}

	/**
	 * @param int $spawnLight
	 *
	 * @return SpawnableTrait
	 */
	public function setSpawnLight(int $spawnLight) : SpawnableTrait {
		$this->spawnLight = $spawnLight;
		return $this;
	}
}