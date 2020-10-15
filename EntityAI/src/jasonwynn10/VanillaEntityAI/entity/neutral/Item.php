<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\neutral;

use jasonwynn10\VanillaEntityAI\entity\Collidable;
use jasonwynn10\VanillaEntityAI\entity\CollisionCheckingTrait;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;

class Item extends ItemEntity implements Collidable {
	use CollisionCheckingTrait;

	/*public function entityBaseTick(int $tickDiff = 1) : bool {
		foreach($this->location->world->getNearbyEntities($this->boundingBox->expandedCopy(0.5,0.5,0.5), $this) as $entity) {
			if($this->pickupDelay === 0 and $entity instanceof Item and $entity->onGround and $this->motion->equals($entity->getMotion()) and $this->item->equals($entity->getItem()) and ($count = $this->item->getCount() + $entity->getItem()->getCount()) <= $this->item->getMaxStackSize()) {
				$this->item->setCount($count);
				$entity->flagForDespawn();
				foreach($this->getViewers() as $player)
					$this->sendSpawnPacket($player);
				break;
			}
		}
		return parent::entityBaseTick($tickDiff);
	}*/

	public function onCollideWithEntity(Entity $entity) : void {
		//TODO: minecart interactions
	}

	public function onCollideWithBlock(Block $block) : void {
		// TODO: hoppers, pressure plates, tripwire
	}

	/**
	 * @param CreatureBase $source
	 */
	public function push(CreatureBase $source) : void { // cannot be pushed
	}
}