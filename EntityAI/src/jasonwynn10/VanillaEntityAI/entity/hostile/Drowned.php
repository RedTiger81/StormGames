<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class Drowned extends Zombie {
	public const NETWORK_ID = EntityLegacyIds::DROWNED;

	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);
	}

	protected function applyGravity() : void {
		if(!$this->isUnderwater()) {
			parent::applyGravity();
		}
	}

	public function getXpDropAmount() : int{
        return mt_rand(4, 8);
	}
}