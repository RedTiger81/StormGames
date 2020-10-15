<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\neutral;

use pocketmine\nbt\tag\CompoundTag;

class HopperMinecart extends Minecart {
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
	 * @return string
	 */
	public function getName() : string {
		return "Armor Stand";
	}
}