<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use jasonwynn10\VanillaEntityAI\entity\ClimbingTrait;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\InventoryHolder;
use jasonwynn10\VanillaEntityAI\entity\ItemHolderTrait;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;
use jasonwynn10\VanillaEntityAI\entity\neutral\Arrow;

use jasonwynn10\VanillaEntityAI\EntityAI;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\World as Level;
use pocketmine\world\Position;
use pocketmine\world\sound\LaunchSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket as TakeItemEntityPacket;

class Skeleton extends MonsterBase implements InventoryHolder {
	use ItemHolderTrait, ClimbingTrait;
	public const NETWORK_ID = EntityLegacyIds::SKELETON;
	public $width = 0.875;
	public $height = 2.0;
	/** @var int */
	protected $moveTime;
	/** @var int */
	protected $attackDelay;
	/** @var float $speed */
	protected $speed = 0.5;

	public function initEntity(CompoundTag $nbt) : void {
		if(!isset($this->mainHand)) {
			$this->mainHand = ItemFactory::get(ItemIds::BOW);
		} // TODO: random enchantments
		// TODO: random armour
		parent::initEntity($nbt);
	}

	public function onUpdate(int $currentTick) : bool {
		if($this->isFlaggedForDespawn() or $this->closed) {
			return false;
		}
		if($this->attackTime > 0) {
			return parent::onUpdate($currentTick);
		}else {
			if($this->moveTime <= 0 and $this->isTargetValid($this->target) and !$this->target instanceof Entity) {
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
			}elseif($this->target instanceof Entity and $this->isTargetValid($this->target->getPosition())) {
				$this->moveTime = 0;
				if($this->location->distance($this->target->getLocation()) <= 16) {
					if($this->attackDelay > 30 and mt_rand(1, 32) < 4) {
						$this->attackDelay = 0;
						$force = 1.2; // TODO: correct speed?
						$yaw = $this->location->yaw + mt_rand(-220, 220) / 10;
						$pitch = $this->location->pitch + mt_rand(-120, 120) / 10;
						$nbt = EntityFactory::createBaseNBT(new Vector3($this->location->x + (-sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * 0.5), $this->location->y + $this->eyeHeight, $this->location->z + (cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * 0.5)), new Vector3(), $yaw, $pitch);
						/** @var Arrow $arrow */
						$arrow = EntityFactory::create(\pocketmine\entity\projectile\Arrow::class, $this->getWorld(), $nbt, $this);
						$arrow->setPickupMode(Arrow::PICKUP_NONE);
						$ev = new EntityShootBowEvent($this, ItemFactory::get(ItemIds::ARROW, 0, 1), $arrow, $force);
						$ev->call();
						$projectile = $ev->getProjectile();
						if($ev->isCancelled()) {
							$projectile->flagForDespawn();
						}elseif($projectile instanceof Projectile) {
							$launch = new ProjectileLaunchEvent($projectile);
							$launch->call();
							if($launch->isCancelled()) {
								$projectile->flagForDespawn();
							}else {
								$projectile->setMotion(new Vector3(-sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * $ev->getForce(), -sin($pitch / 180 * M_PI) * $ev->getForce(), cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * $ev->getForce()));
								$projectile->spawnToAll();
								$this->getWorld()->addSound($this->location, new LaunchSound(), $projectile->getViewers());
							}
						}
					}
					$x = $this->target->getLocation()->x - $this->location->x;
					$z = $this->target->getLocation()->z - $this->location->z;
					$diff = abs($x) + abs($z);
					if($diff > 0) {
						$this->motion->x = $this->speed * 0.15 * ($x / $diff);
						$this->motion->z = $this->speed * 0.15 * ($z / $diff);
					}
					$this->lookAt($this->target->getLocation()->add(0, $this->target->getEyeHeight()));
				}else {
					/*$x = $this->target->getLocation()->x - $this->location->x;
					$y = $this->target->y - $this->location->y;
					$z = $this->target->z - $this->location->z;
					$diff = abs($x) + abs($z);
					if($diff > 0) {
						$this->motion->x = $this->speed * 0.15 * ($x / $diff);
						$this->motion->z = $this->speed * 0.15 * ($z / $diff);
						$this->location->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
					}
					$this->location->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));*/
				}
			}elseif($this->moveTime <= 0) {
				$this->moveTime = 100;
				// TODO: random target position
			}
		}
		return parent::onUpdate($currentTick);
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if($this->moveTime > 0) {
			$this->moveTime -= $tickDiff;
		}
		$time = $this->getWorld()->getTime() % Level::TIME_FULL;
		if(!$this->isOnFire() and ($time < Level::TIME_NIGHT or $time > Level::TIME_SUNRISE) and $this->getWorld()->getBlockSkyLightAt($this->location->getFloorX(), $this->location->getFloorY(), $this->location->getFloorZ()) >= 15) {
			$this->setOnFire(2);
		}
		if($this->isOnFire() and $this->getWorld()->getBlock($this->location, true, false) instanceof Water) { // TODO: check weather
			$this->extinguish();
		}
		$this->attackDelay += $tickDiff;
		return $hasUpdate;
	}

	/**
	 * @return array
	 */
	public function getDrops() : array {
		$drops = parent::getDrops();
		/*if($this->dropAll) {
			$drops = array_merge($drops, $this->armorInventory->getContents());
		}else*/if(mt_rand(1, 100) <= 8.5) {
			if(!empty($this->armorInventory->getContents())) {
				$drops[] = $this->armorInventory->getContents()[array_rand($this->armorInventory->getContents())];
			}
		}
		return $drops;
	}

	/**
	 * @return int
	 */
	public function getXpDropAmount() : int {
		$exp = 3;
		foreach($this->getArmorInventory()->getContents() as $piece)
			$exp += mt_rand(5, 10);
		return $exp;
	}

	/**
	 * @return bool
	 */
	public function canBreathe() : bool{
		return true;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Skeleton";
	}

	/**
	 * @param Position $spawnPos
	 * @param CompoundTag|null $spawnData
	 *
	 * @return null|CreatureBase
	 */
	public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
		// TODO: Implement spawnMob() method.
	}

	/**
	 * @param Position $spawnPos
	 * @param null|CompoundTag $spawnData
	 *
	 * @return null|self
	 */
	public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null, ?string $class = null) : ?CreatureBase {
		// TODO: Implement spawnFromSpawner() method.
	}

	public function onCollideWithEntity(Entity $entity) : void {
		if($entity instanceof \jasonwynn10\VanillaEntityAI\entity\neutral\Item) {
			if($entity->getPickupDelay() > 0 or !$this instanceof InventoryHolder or $this->getWorld()->getDifficulty() <= Level::DIFFICULTY_EASY) {
				return;
			}
			$chance = EntityAI::getInstance()->getRegionalDifficulty($this->getWorld(), $this->chunk);
			if($chance < 50) {
				return;
			}
			$item = $entity->getItem();
			if(!$this->checkItemValueToMainHand($item) and !$this->checkItemValueToOffHand($item)) {
				return;
			}
			$pk = new TakeItemEntityPacket();
			$pk->eid = $this->getId();
			$pk->target = $entity->getId();
			$this->server->broadcastPackets($this->getViewers(), [$pk]);
			$this->setDropAll();
			$this->setPersistence(true);
			if($this->checkItemValueToMainHand($item)) {
				$this->mainHand = clone $item;
			}elseif($this->checkItemValueToOffHand($item)) {
				$this->offHand = clone $item;
			}
		}
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function checkItemValueToMainHand(Item $item) : bool {
		return $this->mainHand === null;
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function checkItemValueToOffHand(Item $item) : bool {
		return false;
	}

	public function equipRandomItems() : void {
	}

	public function equipRandomArmour() : void {
		// TODO: random armour chance by difficulty
	}
}