<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;


use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World as Level;
use pocketmine\world\Position;

abstract class MonsterBase extends CreatureBase {
	public function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);
	}

	/**
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void {
		if($source instanceof EntityDamageByEntityEvent) {
			$this->setTarget($source->getDamager());
		}
		parent::attack($source);
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool {
		$hasUpdate = false;
		if($this->location->world->getDifficulty() <= Level::DIFFICULTY_PEACEFUL) {
			$this->flagForDespawn();
		}
		if($this->target === null) {
			foreach($this->hasSpawned as $player) {
				if($player->isSurvival() and $this->location->distance($player->getLocation()) <= 16 and $this->hasLineOfSight($player)) {
					$this->target = $player;
					$hasUpdate = true;
				}
			}
		}elseif($this->target instanceof Player) {
			if($this->target->isCreative() or !$this->target->isAlive() or $this->location->distance($this->target->getLocation()) > 16 or !$this->hasLineOfSight($this->target)) {
				$this->target = null;
			}
		}elseif($this->target instanceof CreatureBase) {
			if(!$this->target->isAlive() or $this->location->distance($this->target->location) > 16 or !$this->hasLineOfSight($this->target)) {
				$this->target = null;
			}
		}
		return parent::entityBaseTick($tickDiff) ? true : $hasUpdate;
	}

	/**
	 * @param mixed $target
	 *
	 * @return bool
	 */
	protected function isTargetValid($target) : bool {
		if($target instanceof Entity) {
			if($target instanceof Player) {
				return !$target->isFlaggedForDespawn() and !$target->isClosed() and $target->getLocation()->isValid() and $target->isAlive() and $target->isSurvival();
			}
			return !$target->isFlaggedForDespawn() and !$target->isClosed() and $target->getLocation()->isValid() and $target->isAlive();
		}else {
			return $target !== null and $target->isValid();
		}
	}
}