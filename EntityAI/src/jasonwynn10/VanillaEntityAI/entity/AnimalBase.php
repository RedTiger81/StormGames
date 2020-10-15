<?php
declare(strict_types=1);

namespace jasonwynn10\VanillaEntityAI\entity;

use jasonwynn10\VanillaEntityAI\utils\RandomPositionGenerator;
use pocketmine\entity\Ageable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\Position;

abstract class AnimalBase extends CreatureBase implements Ageable{
	use AgeableTrait, PanicableTrait;
	/** @var int $growTime */
	protected $growTime = 200;

	public function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
	}

	/**
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void{
		$this->setPanic();
		$this->setTarget(Position::fromObject(RandomPositionGenerator::findRandomTargetBlock($this, 5, 4), $this->getWorld()));

		parent::attack($source);
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->growTime -= $tickDiff <= 0){
			$this->setBaby(false);
		}
		if($this->moveTime > 0){
			$this->moveTime -= $tickDiff;
		}

		$this->entityBaseTickForce($tickDiff);

		return parent::entityBaseTick($tickDiff);
	}

	public function onUpdate(int $currentTick) : bool{
		$return = parent::onUpdate($currentTick);

		if($this->isInPanic() && $currentTick % 25 === 0){
			$this->setTarget(Position::fromObject(RandomPositionGenerator::findRandomTargetBlock($this, 5, 4), $this->getWorld()));
		}

		if($this->target instanceof Position and $this->target->isValid()){
			if($this->moveTime <= 0 || $this->isInPanic()){
				$this->stepMove();
			}
		}elseif($this->moveTime <= 0){
			$this->moveTime = 100;
			$this->setTarget(Position::fromObject(RandomPositionGenerator::findRandomTargetBlock($this, 8, 4), $this->getWorld()));
		}

		return $return;
	}
}