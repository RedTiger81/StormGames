<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use jasonwynn10\VanillaEntityAI\entity\AgeableTrait;
use jasonwynn10\VanillaEntityAI\entity\ClimbingTrait;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\InventoryHolder;
use jasonwynn10\VanillaEntityAI\entity\ItemHolderTrait;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;
use jasonwynn10\VanillaEntityAI\EntityAI;
use pocketmine\block\Water;
use pocketmine\entity\Ageable;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\biome\Biome;
use pocketmine\world\World as Level;
use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\player\Player;

class Zombie extends MonsterBase implements Ageable, InventoryHolder {
	use ItemHolderTrait, AgeableTrait, ClimbingTrait;
	public const NETWORK_ID = EntityLegacyIds::ZOMBIE;
	public $width = 0.6;
	public $height = 1.95;
	/** @var int */
	protected $attackDelay;
	/** @var float $speed */
	protected $speed = 1.5;

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);

		if(mt_rand(1, 100) < 6) {
			$this->setBaby();
			if(mt_rand(1, 100) <= 15) {
				// TODO: zombie jockey
			}else {
				// TODO: check nearby chickens
			}
		}
		if(mt_rand(1, 100) >= 80) {
			if((bool) mt_rand(0, 1)) {
				$this->equipRandomItems();
			}else {
				$this->equipRandomArmour();
			}
		}
	}

	public function equipRandomItems() : void {
		$this->setMainHandItem(ItemFactory::get(ItemIds::WOODEN_SWORD));
	}

	public function equipRandomArmour() : void {
		$this->getArmorInventory()->setHelmet(ItemFactory::get(ItemIds::CHAIN_HELMET));
	}

	public function attack(EntityDamageEvent $source) : void {
		if($source->getCause() === EntityDamageEvent::CAUSE_DROWNING and $this->getHealth() - $source->getFinalDamage() <= 0) {
			/** @var Drowned|null $entity */
			$entity = EntityFactory::create(Drowned::class, $this->location->world, EntityFactory::createBaseNBT($this->location, $this->motion, $this->location->yaw, $this->location->pitch));
			$entity->setMainHandItem($this->mainHand);
			$entity->setOffHandItem($this->offHand);
			$this->getWorld()->addEntity($entity);
			$this->getWorld()->broadcastLevelEvent($this->location, LevelSoundEventPacket::SOUND_CONVERT_TO_DROWNED, 0);
		}
		// TODO: 10% chance to resist knockback.
		parent::attack($source);
	}

	public function onUpdate(int $currentTick) : bool {
		if($this->isFlaggedForDespawn() or $this->closed) {
			return false;
		}
		if($this->attackTime > 0) {
			return parent::onUpdate($currentTick);
		}else {
			if($this->moveTime <= 0 and $this->isTargetValid($this->target) and !$this->target instanceof Entity) {
				$this->stepMove();
			}elseif($this->target instanceof Entity and $this->isTargetValid($this->target->getPosition())) {
				$this->moveTime = 0;
				$x = $this->target->getLocation()->x - $this->location->x;
				$y = $this->target->getLocation()->y - $this->location->y;
				$z = $this->target->getLocation()->z - $this->location->z;
				$diff = abs($x) + abs($z);
				if($diff > 0) {
					$this->motion->x = $this->speed * 0.15 * ($x / $diff);
					$this->motion->z = $this->speed * 0.15 * ($z / $diff);
					$this->location->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
				}
				$this->location->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
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
		if(!$this->isOnFire() and ($time < Level::TIME_NIGHT or $time > Level::TIME_SUNRISE) and $this->location->world->getBlockSkyLightAt($this->location->getFloorX(), $this->location->getFloorY(), $this->location->getFloorZ()) >= 15) {
			$this->setOnFire(2);
		}
		if($this->isOnFire() and $this->location->world->getBlock($this->location, true, false) instanceof Water) { // TODO: check weather
			$this->extinguish();
		}
		$this->attackDelay++;
		return $hasUpdate;
	}

	/**
	 * @return array
	 */
	public function getDrops() : array {
		$drops = [
			ItemFactory::get(ItemIds::ROTTEN_FLESH, 0, mt_rand(0, 2))
		];
		if(mt_rand(0, 199) < 5) {
			switch(mt_rand(0, 2)) {
				case 0:
					$drops[] = ItemFactory::get(ItemIds::IRON_INGOT, 0, 1);
				break;
				case 1:
					$drops[] = ItemFactory::get(ItemIds::CARROT, 0, 1);
				break;
				case 2:
					$drops[] = ItemFactory::get(ItemIds::POTATO, 0, 1);
				break;
			}
		}
		if($this->dropAll) {
			$drops = array_merge($drops, $this->armorInventory->getContents());
		}elseif(mt_rand(1, 100) <= 8.5) {
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
		if($this->baby) {
			$exp = 12;
		}else {
			$exp = 5;
		}
		foreach($this->getArmorInventory()->getContents() as $piece)
			$exp += mt_rand(5, 10);
		return $exp;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Zombie";
	}

	/**
	 * @param Player $player
	 */
	public function onCollideWithPlayer(Player $player) : void {
		if($this->target === $player and $this->attackDelay > 10) {
			$this->attackDelay = 0;
			$damage = 2;
			switch($this->getWorld()->getDifficulty()) {
				case Level::DIFFICULTY_EASY:
					$damage = 2;
				break;
				case Level::DIFFICULTY_NORMAL:
					$damage = 3;
				break;
				case Level::DIFFICULTY_HARD:
					$damage = 4;
			}
			if($this->mainHand !== null) {
				$damage = $this->mainHand->getAttackPoints();
			}
			$pk = new ActorEventPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->event = ActorEventPacket::ARM_SWING;
			$this->server->broadcastPackets($this->hasSpawned, [$pk]);
			$player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
		}
	}

	/**
	 * @param Position $spawnPos
	 * @param CompoundTag|null $spawnData
	 *
	 * @return null|CreatureBase
	 */
	public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
		$nbt = EntityFactory::createBaseNBT($spawnPos);
		if(isset($spawnData)) {
			$nbt = $spawnData->merge($nbt);
			$nbt->setInt("id", self::NETWORK_ID);
		}
		if($spawnPos->getWorld()->getBiomeId($spawnPos->x, $spawnPos->z) === Biome::DESERT and mt_rand(1, 100) > 80) {
			/** @var Husk $entity */
			$entity = EntityFactory::create(Husk::class, $spawnPos->getWorld(), $nbt);
		}else {
			/** @var self $entity */
			$entity = EntityFactory::create(self::class, $spawnPos->getWorld(), $nbt);
		}
		// TODO: work on logic here more
		if(!$spawnPos->isValid() or count($entity->getBlocksAround()) > 1 or $spawnPos->getWorld()->getFullLight($spawnPos) > $entity->spawnLight) {
			$entity->flagForDespawn();
			return null;
		}else {
			$entity->spawnToAll();
			return $entity;
		}
	}

	/**
	 * @param Entity $entity
	 */
	public function onCollideWithEntity(Entity $entity) : void {
		if($this->target === $entity and $this->attackDelay > 10) {
			$this->attackDelay = 0;
			$damage = 2;
			switch($this->getWorld()->getDifficulty()) {
				case Level::DIFFICULTY_EASY:
					$damage = 2;
				break;
				case Level::DIFFICULTY_NORMAL:
					$damage = 3;
				break;
				case Level::DIFFICULTY_HARD:
					$damage = 4;
			}
			if($this->mainHand !== null) {
				$damage = $this->mainHand->getAttackPoints();
			}
			$pk = new ActorEventPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->event = ActorEventPacket::ARM_SWING;
			$this->server->broadcastPackets($this->hasSpawned, [$pk]);
			$entity->attack(new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
		}
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
			$pk = new TakeItemActorPacket();
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
		// TODO: Implement checkItemValueToMainHand() method.
		return true;
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function checkItemValueToOffHand(Item $item) : bool {
		// TODO: Implement checkItemValueToOffHand() method.
		return true;
	}
}