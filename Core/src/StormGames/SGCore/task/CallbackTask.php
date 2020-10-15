<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\scheduler\Task;

class CallbackTask extends Task{
	/** @var callable */
	private $callable;
	/** @var mixed[] */
	private $args = [];

	public function __construct(callable $callable, array $args){
		$this->callable = $callable;
		$this->args = $args;
	}

	/**
	 * @return callable
	 */
	public function getCallable() : callable{
		return $this->callable;
	}

	/**
	 * @return mixed[]
	 */
	public function getArgs() : array{
		return $this->args;
	}

	public function onRun(int $currentTick) : void{
	    $args = $this->args;
	    $args[] = $this->getTaskId();
		call_user_func_array($this->callable, $args);
	}

}