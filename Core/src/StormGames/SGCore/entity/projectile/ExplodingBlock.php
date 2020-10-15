<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity\projectile;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;

class ExplodingBlock extends ThrowableBlock{

	public const TAG_EXPLODE_SIZE = "ExplodeSize"; // int

	/** @var int */
	protected $size;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->size = $nbt->getInt(self::TAG_EXPLODE_SIZE, 1);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$source = $this->location->floor();

		$explosionSize = $this->size * 2;
		$minX = (int) floor($this->location->x - $explosionSize - 1);
		$maxX = (int) ceil($this->location->x + $explosionSize + 1);
		$minY = (int) floor($this->location->y - $explosionSize - 1);
		$maxY = (int) ceil($this->location->y + $explosionSize + 1);
		$minZ = (int) floor($this->location->z - $explosionSize - 1);
		$maxZ = (int) ceil($this->location->z + $explosionSize + 1);

		$explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

		$owningEntity = $this->getOwningEntity();
		$list = $this->location->getWorld()->getNearbyEntities($explosionBB, $this->getOwningEntity());
		foreach($list as $entity){
			$distance = $entity->location->distance($this->location) / $explosionSize;

			if($distance <= 1){
				$motion = $entity->subtract($this)->normalize();

				$impact = (1 - $distance) * ($exposure = 1);

				$damage = (int) ((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

				if($owningEntity instanceof Entity){
					$ev = new EntityDamageByEntityEvent($owningEntity, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
				}else{
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}

				$entity->attack($ev);
				$entity->setMotion($motion->location->multiply($impact));
			}
		}

		//$this->location->getWorld()->broadcastLevelEvent($source, LevelEventPacket::EVENT_PARTICLE_DESTROY, $this->getNetworkProperties()->getInt(self::DATA_MINECART_DISPLAY_BLOCK));
	}

	/**
	 * @param int $size
	 */
	public function setSize(int $size) : void{
		$this->size = $size;
	}

	/**
	 * @return int
	 */
	public function getSize() : int{
		return $this->size;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt(self::TAG_EXPLODE_SIZE, $this->size);
		return $nbt;
	}
}