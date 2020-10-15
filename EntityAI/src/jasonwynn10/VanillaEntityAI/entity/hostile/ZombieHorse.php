<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;
use pocketmine\entity\EntityFactory;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;

class ZombieHorse extends MonsterBase {
	public const NETWORK_ID = EntityLegacyIds::ZOMBIE_HORSE;
	public $width = 1.3;
	public $height = 1.5;

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt); // TODO: Change the autogenerated stub
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
		return parent::getDrops(); // TODO: Change the autogenerated stub
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Zombie Horse";
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
}