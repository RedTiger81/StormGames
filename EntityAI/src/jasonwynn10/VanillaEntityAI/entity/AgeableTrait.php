<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;

trait AgeableTrait {
	public $baby = false;

	/**
	 * @param bool $baby
	 *
	 * @return self
	 */
	public function setBaby(bool $baby = true) : self {
		$this->baby = $baby;
		$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::BABY, $baby);
		$this->setSprinting();
		$this->setScale($baby ? 0.5 : 1);
		return $this;
	}


	public function isBaby() : bool{
		return $this->baby;
	}
}