<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\tile;

use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\SpawnableTrait;
use jasonwynn10\VanillaEntityAI\EntityAI;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\World as Level;
use pocketmine\world\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\block\tile\Spawnable;

class MobSpawner extends Spawnable {

	public const IS_MOVABLE = "isMovable"; // ByteTag
	public const DELAY = "Delay"; // ShortTag
	public const MAX_NEARBY_ENTITIES = "MaxNearbyEntities"; // ShortTag
	public const MAX_SPAWN_DELAY = "MaxSpawnDelay"; // ShortTag
	public const MIN_SPAWN_DELAY = "MinSawnDelay"; // ShortTag
	public const REQUIRED_PLAYER_RANGE = "RequiredPlayerRange"; // ShortTag
	public const SPAWN_COUNT = "SpawnCount"; // ShortTag
	public const SPAWN_RANGE = "SpawnRange"; // ShortTag
	public const ENTITY_ID = "EntityId"; // IntTag
	public const DISPLAY_ENTITY_HEIGHT = "DisplayEntityHeight"; // FloatTag
	public const DISPLAY_ENTITY_SCALE = "DisplayEntityScale"; // FloatTag
	public const DISPLAY_ENTITY_WIDTH = "DisplayEntityWidth"; // FloatTag
	/** @var int $spawnRange */
	protected $spawnRange = 2;
	/** @var int $maxNearbyEntities */
	protected $maxNearbyEntities = 3;
	/** @var int $requiredPlayerRange */
	protected $requiredPlayerRange = 16;
	/** @var int $delay */
	public $delay = -1;
	/** @var int $minSpawnDelay */
	public $minSpawnDelay = 200;
	/** @var int $maxSpawnDelay */
	protected $maxSpawnDelay = 220;//800;
	/** @var int $spawnCount */
	protected $spawnCount = 3;
	/** @var AxisAlignedBB|null $spawnArea */
	protected $spawnArea;
	/** @var bool $isMovable */
	protected $isMovable = false;
	/** @var int $entityId */
	protected $entityId = -1;
	/** @var float $displayHeight */
	protected $displayHeight = 0.9;
	/** @var float $displayScale */
	protected $displayScale = 0.5;
	/** @var float $displayWidth */
	protected $displayWidth = 0.3;

	public function __construct(Level $world, Vector3 $pos, int $minSpawnDelay = 500){
		parent::__construct($world, $pos);
		$this->spawnArea = new AxisAlignedBB($pos->x - $this->spawnRange, $pos->y - 1, $pos->z - $this->spawnRange, $pos->x + $this->spawnRange, $pos->y + 1, $pos->z + $this->spawnRange);
		$this->minSpawnDelay = $minSpawnDelay * 20;
		$this->maxSpawnDelay = $minSpawnDelay * 20 + 10;
	}

	/**
	 * @return bool
	 */
	public function onUpdate() : bool {
		if($this->isClosed() or $this->entityId < EntityLegacyIds::CHICKEN) { // TODO: are there entities with ids less than 10?
			return false;
		}
		if(--$this->delay === 0) {
			$this->delay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
			$valid = $this->getPos()->getWorld()->getPlayers() > 0;
			foreach(EntityAI::getEntities() as $class => $arr) {
				if($class::NETWORK_ID === $this->entityId) {
					if($valid and count(self::getAreaEntities($this->spawnArea, $this->getPos()->getWorld(), $class)) < $this->maxNearbyEntities) {
						$spawned = 0;
						while($spawned < $this->spawnCount) {
							SpawnableTrait::spawnFromSpawner($this->getRandomSpawnPos(), null, $class);
							$spawned++;
						}
					}
					break;
				}
			}
		}elseif($this->delay === -1) {
			$this->delay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
		}
		return true;
	}

	/**
	 * @param AxisAlignedBB $bb
	 * @param Level $level
	 * @param string $type
	 *
	 * @return array
	 */
	protected static function getAreaEntities(AxisAlignedBB $bb, Level $level, string $type = Living::class) {
		$nearby = [];
		$minX = ((int)floor($bb->minX)) >> 4; // TODO: check if this is right
		$maxX = ((int)floor($bb->maxX)) >> 4;
		$minZ = ((int)floor($bb->minZ)) >> 4;
		$maxZ = ((int)floor($bb->maxZ)) >> 4;
		for($x = $minX; $x <= $maxX; ++$x) {
			for($z = $minZ; $z <= $maxZ; ++$z) {
				$chunk = $level->getChunk($x, $z);
				if($chunk === null) continue;
				foreach($chunk->getEntities() as $entity) {
					/** @var Entity|null $entity */
					if($entity instanceof $type and $entity->boundingBox->intersectsWith($bb)) {
						$nearby[] = $entity;
					}
				}
			}
		}
		return $nearby;
	}

	/**
	 * Returns a randomized position within the spawner spawn range
	 *
	 * @return Position returns valid y coordinate if found
	 */
	protected function getRandomSpawnPos() : Position {
		$x = mt_rand(intval(floor($this->spawnArea->minX)), intval(floor($this->spawnArea->maxX)));
		$y = mt_rand(intval(floor($this->spawnArea->minY)), intval(floor($this->spawnArea->maxY)));
		$z = mt_rand(intval(floor($this->spawnArea->minZ)), intval(floor($this->spawnArea->maxZ)));
		return new Position($x + 0.5, $y, $z + 0.5, $this->getPos()->getWorld());
	}

	/**
	 * Reads additional data from the CompoundTag on tile creation.
	 *
	 * @param CompoundTag $nbt
	 */
	public function readSaveData(CompoundTag $nbt) : void {
		if($nbt->hasTag(self::ENTITY_ID, IntTag::class)) {
			$this->entityId = $nbt->getInt(self::ENTITY_ID);
		}
		if($nbt->hasTag(self::SPAWN_COUNT, ShortTag::class)) {
			$this->spawnCount = $nbt->getShort(self::SPAWN_COUNT);
		}
		if($nbt->hasTag(self::SPAWN_RANGE, ShortTag::class)) {
			$this->spawnRange = $nbt->getShort(self::SPAWN_RANGE);
		}
		$this->spawnArea = new AxisAlignedBB($this->getPos()->x - $this->spawnRange, $this->getPos()->y - 1, $this->getPos()->z - $this->spawnRange, $this->getPos()->x + $this->spawnRange, $this->getPos()->y + 1, $this->getPos()->z + $this->spawnRange);
		if($nbt->hasTag(self::DELAY, ShortTag::class)) {
			$this->delay = $nbt->getShort(self::DELAY);
		}
		if($nbt->hasTag(self::MIN_SPAWN_DELAY, ShortTag::class)) {
			$this->minSpawnDelay = $nbt->getShort(self::MIN_SPAWN_DELAY);
		}
		if($nbt->hasTag(self::MAX_SPAWN_DELAY, ShortTag::class)) {
			$this->maxSpawnDelay = $nbt->getShort(self::MAX_SPAWN_DELAY);
		}
		if($nbt->hasTag(self::MAX_NEARBY_ENTITIES, ShortTag::class)) {
			$this->maxNearbyEntities = $nbt->getShort(self::MAX_NEARBY_ENTITIES);
		}
		if($nbt->hasTag(self::REQUIRED_PLAYER_RANGE, ShortTag::class)) {
			$this->requiredPlayerRange = $nbt->getShort(self::REQUIRED_PLAYER_RANGE);
		}
		if($nbt->hasTag(self::DISPLAY_ENTITY_HEIGHT, FloatTag::class)) {
			$this->displayHeight = $nbt->getFloat(self::DISPLAY_ENTITY_HEIGHT);
		}
		if($nbt->hasTag(self::DISPLAY_ENTITY_WIDTH, FloatTag::class)) {
			$this->displayHeight = $nbt->getFloat(self::DISPLAY_ENTITY_WIDTH);
		}
		if($nbt->hasTag(self::DISPLAY_ENTITY_SCALE, FloatTag::class)) {
			$this->displayHeight = $nbt->getFloat(self::DISPLAY_ENTITY_SCALE);
		}
	}

	/**
	 * Writes additional save data to a CompoundTag, not including generic things like ID and coordinates.
	 *
	 * @param CompoundTag $nbt
	 */
	protected function writeSaveData(CompoundTag $nbt) : void {
		$this->addAdditionalSpawnData($nbt);
	}

	/**
	 * An extension to getSpawnCompound() for
	 * further modifying the generic tile NBT.
	 *
	 * @param CompoundTag $nbt
	 */
	protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
		$nbt->setByte(self::IS_MOVABLE, (int)$this->isMovable);
		$nbt->setShort(self::DELAY, $this->delay);
		$nbt->setShort(self::MAX_NEARBY_ENTITIES, $this->maxNearbyEntities);
		$nbt->setShort(self::MAX_SPAWN_DELAY, $this->maxSpawnDelay);
		$nbt->setShort(self::MIN_SPAWN_DELAY, $this->minSpawnDelay);
		$nbt->setShort(self::REQUIRED_PLAYER_RANGE, $this->requiredPlayerRange);
		$nbt->setShort(self::SPAWN_COUNT, $this->spawnCount);
		$nbt->setShort(self::SPAWN_RANGE, $this->spawnRange);
		$nbt->setInt(self::ENTITY_ID, $this->entityId);
		$nbt->setFloat(self::DISPLAY_ENTITY_HEIGHT, $this->displayHeight);
		$nbt->setFloat(self::DISPLAY_ENTITY_WIDTH, $this->displayWidth);
		$nbt->setFloat(self::DISPLAY_ENTITY_SCALE, $this->displayScale);
	}

	/**
	 * @return int
	 */
	public function getEntityId() : int {
		return $this->entityId;
	}

	/**
	 * @param int $eid
	 *
	 * @return MobSpawner
	 */
	public function setEntityId(int $eid) : MobSpawner {
		$this->entityId = $eid;
		$this->delay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
		$this->setDirty();
		$this->onUpdate();
		return $this;
	}

	/**
	 * @param int $minDelay
	 *
	 * @return MobSpawner
	 */
	public function setMinSpawnDelay(int $minDelay) : MobSpawner {
		if($minDelay < $this->maxSpawnDelay and !($minDelay < -0x8000 or $minDelay > 0x7fff)) {
			$this->minSpawnDelay = $minDelay;
		}
		return $this;
	}

	/**
	 * @param int $maxDelay
	 *
	 * @return MobSpawner
	 */
	public function setMaxSpawnDelay(int $maxDelay) : MobSpawner {
		if($this->minSpawnDelay < $maxDelay and $maxDelay !== 0 and !($maxDelay < -0x8000 or $maxDelay > 0x7fff)) {
			$this->maxSpawnDelay = $maxDelay;
		}
		return $this;
	}

	/**
	 * @param int $delay
	 *
	 * @return MobSpawner
	 */
	public function setSpawnDelay(int $delay) : MobSpawner {
		if($delay < $this->maxSpawnDelay and $delay > $this->minSpawnDelay and !($delay < -0x8000 or $delay > 0x7fff)) {
			$this->delay = $delay;
		}
		return $this;
	}

	/**
	 * @param int $range
	 *
	 * @return MobSpawner
	 */
	public function setRequiredPlayerRange(int $range) : MobSpawner {
		if($range < 0) {
			$range = 0;
		}
		$this->requiredPlayerRange = $range;
		return $this;
	}

	/**
	 * @param int $count
	 *
	 * @return MobSpawner
	 */
	public function setMaxNearbyEntities(int $count) : MobSpawner {
		$this->maxNearbyEntities = $count;
		return $this;
	}

	/**
	 * @param bool $isMovable
	 *
	 * @return MobSpawner
	 */
	public function setMovable(bool $isMovable = true) : MobSpawner {
		$this->isMovable = $isMovable;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isMovable() : bool {
		return $this->isMovable;
	}
}