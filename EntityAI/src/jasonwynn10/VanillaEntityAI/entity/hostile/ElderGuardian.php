<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class ElderGuardian extends Guardian {
	public const NETWORK_ID = EntityLegacyIds::ELDER_GUARDIAN;
	public $width = 1.9975;
	public $height = 1.9975;

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt); // TODO: Change the autogenerated stub
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
		return "Elder Guardian";
	}
}