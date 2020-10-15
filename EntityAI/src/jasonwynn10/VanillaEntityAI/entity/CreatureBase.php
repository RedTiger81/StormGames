<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds as BlockIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\timings\Timings;
use pocketmine\math\Facing;
use pocketmine\world\World;

abstract class CreatureBase extends Living implements Linkable, Collidable, Lookable {
	use SpawnableTrait, CollisionCheckingTrait, LinkableTrait;
	/** @var float $speed */
	protected $speed = 1.0;
	/** @var float $stepHeight */
	protected $stepHeight = 1.0;
	/** @var Position|null $target */
	protected $target = null;
	/** @var bool $persistent */
	protected $persistent = false;
	/** @var int $moveTime */
	protected $moveTime = 0;
	/** @var int $idleTime */
	protected $idleTime = 0;
	protected $ySize = 0;
	/** @var Random */
	public $random;

	public function __construct(World $world, CompoundTag $nbt){
		parent::__construct($world, $nbt);
		$this->random = new Random();
	}

	/**
	 * Returns the Vector3 side number right of the specified one
	 *
	 * @param int $side 0-5 one of the Vector3::SIDE_* constants
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException if an invalid side is supplied
	 */
	public static function getRightSide(int $side) : int {
		if($side >= 0 and $side <= 5) {
			return $side ^ 0x03; // TODO: right now it gives the opposite side...
		}
		throw new \InvalidArgumentException("Invalid side $side given to getRightSide");
	}

	/**
	 * @param Position $spawnPos
	 * @param CompoundTag|null $spawnData
	 *
	 * @return null|CreatureBase
	 */
	public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
		return null;
	}

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);
	}

	public function lookAround() : void {
		$entities = $this->location->getWorld()->getNearbyEntities($this->boundingBox->expandedCopy(8,2,8), $this);
		$entities = array_filter($entities,function(Entity $entity){
			if($entity->isAlive() or !$entity->isFlaggedForDespawn() and $entity instanceof Player) {
				return true;
			}
			return false;
		});
		$yaw = $this->location->yaw;
		$pitch = $this->location->pitch;
		if(!empty($entities) and mt_rand(1,3) === 1) {
			/** @var Player $player */
			$player = $entities[array_rand($entities)];
			$this->lookAt($player->getPosition()->asVector3()->add(0, $player->height));
		}else{
			// rotate the entity: 0 degrees is south and increases clockwise
			$yaw = mt_rand(0, 1) ? $yaw + mt_rand(15, 45) : $yaw - mt_rand(15, 45);
			if($yaw > 360){
				$yaw = 360;
			}else if($yaw < 0){
				$yaw = 0;
			}
			// 0 degrees is horizontal, -90 is up, 90 is down. but 90 degrees looks very silly - so 60 degrees is completely ok
			$pitch = mt_rand(0, 1) ? $pitch + mt_rand(10, 20) : $pitch - mt_rand(10, 20);
			if($pitch > 60){
				$pitch = 60;
			}else if($pitch < -60){
				$pitch = -60;
			}
		}

		$this->setRotation($yaw, $pitch);
	}

	/**
	 * @param float $dx
	 * @param float $dy
	 * @param float $dz
	 */
	public function move(float $dx, float $dy, float $dz) : void {
		$this->blocksAround = null;
		Timings::$entityMoveTimer->startTiming();
		$movX = $dx;
		$movY = $dy;
		$movZ = $dz;
		if($this->keepMovement) {
			$this->boundingBox->offset($dx, $dy, $dz);
		}else {
			$this->ySize *= 0.4;
			$axisalignedbb = clone $this->boundingBox;
			$list = $this->location->world->getCollisionBoxes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);
			foreach($list as $bb) {
				$dy = $bb->calculateYOffset($this->boundingBox, $dy);
			}
			$this->boundingBox->offset(0, $dy, 0);
			$fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));
			foreach($list as $bb) {
				$dx = $bb->calculateXOffset($this->boundingBox, $dx);
			}
			$this->boundingBox->offset($dx, 0, 0);
			foreach($list as $bb) {
				$dz = $bb->calculateZOffset($this->boundingBox, $dz);
			}
			$this->boundingBox->offset(0, 0, $dz);
			if($this->stepHeight > 0 and $fallingFlag and $this->ySize < 0.05 and ($movX != $dx or $movZ != $dz)) {
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;
				$axisalignedbb1 = clone $this->boundingBox;
				$this->boundingBox = $axisalignedbb;
				$list = $this->getWorld()->getCollisionBoxes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);
				foreach($list as $bb) {
					$dy = $bb->calculateYOffset($this->boundingBox, $dy);
				}
				$this->boundingBox->offset(0, $dy, 0);
				foreach($list as $bb) {
					$dx = $bb->calculateXOffset($this->boundingBox, $dx);
				}
				$this->boundingBox->offset($dx, 0, 0);
				foreach($list as $bb) {
					$dz = $bb->calculateZOffset($this->boundingBox, $dz);
				}
				$this->boundingBox->offset(0, 0, $dz);
				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)) {
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
					$this->boundingBox = $axisalignedbb1;
				}else {
					$block = $this->location->world->getBlock($this->location->getSide(Facing::DOWN));
					$blockBB = new AxisAlignedBB($block->getPos()->x, $block->getPos()->y, $block->getPos()->z, $block->getPos()->x + 1, $block->getPos()->y + 1, $block->getPos()->z + 1);
					$this->ySize += $blockBB->maxY - $blockBB->minY;
				}
			}
		}
		$this->location->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->location->y = $this->boundingBox->minY - $this->ySize;
		$this->location->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;
		$this->checkChunks();
		$this->checkBlockCollision();
		$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
		$this->updateFallState($dy, $this->onGround);
		if($movX != $dx) {
			$this->motion->x = 0;
		}
		if($movY != $dy) {
			$this->motion->y = 0;
		}
		if($movZ != $dz) {
			$this->motion->z = 0;
		}
		//TODO: vehicle collision events (first we need to spawn them!)
		Timings::$entityMoveTimer->stopTiming();
	}

	/**
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function hasLineOfSight(Entity $entity) : bool {
		$distance = (int) $this->location->add(0, $this->eyeHeight)->distance($entity->getLocation());
		if($distance > 1) {
			$blocksBetween = $this->getLineOfSight($distance, 0, [
				BlockIds::AIR => BlockIds::AIR,
				BlockIds::WATER => BlockIds::WATER,
				BlockIds::LAVA => BlockIds::LAVA
			]);
			return empty(array_filter($blocksBetween, function(Block $block) {
				return !in_array($block->getId(), [BlockIds::AIR, BlockIds::WATER, BlockIds::LAVA]);
			}));
		}
		return true;
	}

	/**
	 * @return Position|null
	 */
	public function getTarget() : ?Position {
		return $this->target;
	}

	/**
	 * @param mixed $target
	 *
	 * @return CreatureBase
	 */
	public function setTarget($target) : self {
		if($target instanceof Entity or is_null($target)) {
			$this->setTargetEntity($target);
		}

		$this->target = $target;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getSpeed() : float {
		return $this->speed;
	}

	/**
	 * @param float $speed
	 *
	 * @return CreatureBase
	 */
	public function setSpeed(float $speed) : self {
		$this->speed = $speed;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPersistent() : bool {
		return $this->persistent;
	}

	/**
	 * @param bool $persistent
	 *
	 * @return CreatureBase
	 */
	public function setPersistence(bool $persistent) : self {
		$this->persistent = $persistent;
		return $this;
	}

	/**
	 * @param Player $player
	 */
	public function onPlayerLook(Player $player) : void {
		// TODO: Implement onPlayerLook() method.
	}

	/**
	 * @param Entity $entity
	 */
	public function onCollideWithEntity(Entity $entity) : void {
	}

	/**
	 * @param Block $block
	 */
	public function onCollideWithBlock(Block $block) : void {
	}

	public function push(CreatureBase $source) : void {
		//Get speed of that close player
		$sourceSpeed = abs($source->getMotion()->x) + abs($source->getMotion()->z);
		$selfSpeed = abs($this->getMotion()->x) + abs($this->getMotion()->z);
		//If player speed is superior to X
		if ($sourceSpeed > $selfSpeed)
		{
			$this->knockBack($this->location->x - $source->location->x, $this->location->z - $source->location->z, 0.3);
			$source->knockBack($source->location->x - $this->location->x, $source->location->z - $this->location->z, 0.1);
		}else{
			$this->knockBack($this->location->x - $source->location->x, $this->location->z - $source->location->z, 0.2);
			$source->knockBack($source->location->x - $this->location->x, $source->location->z - $this->location->z, 0.1);
		}
	}

	public function stepMove(): void{
		$x = $this->target->x - $this->location->x;
		$y = $this->target->y - $this->location->y;
		$z = $this->target->z - $this->location->z;
		$diff = abs($x) + abs($z);
		if($diff > 0) {
			$this->motion->x = $this->speed * 0.15 * ($x / $diff);
			$this->motion->z = $this->speed * 0.15 * ($z / $diff);
			$this->location->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
		}
		$this->location->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
		if($this->location->distance($this->target) <= 0) {
			$this->target = null;
		}
	}
}