<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;


trait PanicableTrait {
	/** @var int $panicTime */
	protected $panicTime = 100;
	/** @var bool $inPanic */
	protected $inPanic = false;

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTickForce(int $tickDiff = 1) : void {
		if($this->inPanic){
			$this->panicTime -= $tickDiff;
			if($this->panicTime <= 0) {
				$this->setPanic(false);
				$this->setTarget(null);
			}
		}
	}

	/**
	 * @param bool $panic
	 */
	public function setPanic(bool $panic = true) : void {
		if($this->inPanic !== $panic){
			$this->setSpeed($panic ? $this->getSpeed() * 1.3 : $this->getSpeed() / 1.3);
			$this->inPanic = $panic;
			$this->panicTime = 100;
			if($panic) {
				$this->moveTime = 0;
			}
		}
	}

	/**
	 * @return bool
	 */
	public function isInPanic() : bool {
		return $this->inPanic;
	}
}