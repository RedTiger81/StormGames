<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\block\Block;
use pocketmine\entity\Living;

trait CollisionCheckingTrait {
	protected function checkBlockCollision() : void {
		$vector = $this->temporalVector->setComponents(0, 0, 0);
		/** @var Block $block */
		foreach($this->getBlocksAround() as $block) {
			$block->onEntityInside($this);
			$this->onCollideWithBlock($block);
			$block->addVelocityToEntity($this, $vector);
		}
		if($vector->lengthSquared() > 0) {
			$vector = $vector->normalize();
			$d = 0.014;
			$this->motion->x += $vector->x * $d;
			$this->motion->y += $vector->y * $d;
			$this->motion->z += $vector->z * $d;
		}
	}
}