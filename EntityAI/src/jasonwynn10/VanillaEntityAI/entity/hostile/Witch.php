<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use jasonwynn10\VanillaEntityAI\entity\Collidable;
use jasonwynn10\VanillaEntityAI\entity\CollisionCheckingTrait;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\InventoryHolder;
use jasonwynn10\VanillaEntityAI\entity\ItemHolderTrait;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;

class Witch extends MonsterBase implements Collidable, InventoryHolder {
	use CollisionCheckingTrait, ItemHolderTrait;
	public const NETWORK_ID = EntityLegacyIds::WITCH;
	public $width = 0.6;
	public $height = 1.95;

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff); // TODO: Change the autogenerated stub
	}

	/**
	 * @return array
	 */
	public function getDrops() : array {
		$drops = parent::getDrops();
		// TODO: chance drop potion
		return $drops;
	}

	/**
	 * @return int
	 */
	public function getXpDropAmount() : int {
		return 5;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Witch";
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
	 * @return null|CreatureBase
	 */
	public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null, ?string $class= null) : ?CreatureBase {
		// TODO: Implement spawnFromSpawner() method.
	}

	/**
	 * @param Entity $entity
	 */
	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
	}

	public function equipRandomItems() : void {
		// TODO: Implement equipRandomItems() method.
	}

	public function equipRandomArmour() : void {
		// TODO: Implement equipRandomArmour() method.
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function checkItemValueToMainHand(Item $item) : bool {
		// TODO: Implement checkItemValueToMainHand() method.
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function checkItemValueToOffHand(Item $item) : bool {
		// TODO: Implement checkItemValueToOffHand() method.
	}
}