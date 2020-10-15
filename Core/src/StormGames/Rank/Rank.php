<?php
/*
 *  _____               _               ___   ___  __ 
 * /__   \___  _ __ ___| |__   /\/\    / __\ / _ \/__\
 *   / /\/ _ \| '__/ __| '_ \ /    \  / /   / /_)/_\  
 *  / / | (_) | | | (__| | | / /\/\ \/ /___/ ___//__  
 *  \/   \___/|_|  \___|_| |_\/    \/\____/\/   \__/
 *
 * (C) Copyright 2019 TorchMCPE (http://torchmcpe.fun/) and others.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Contributors:
 * - Eren Ahmet Akyol
 */
declare(strict_types=1);

namespace StormGames\Rank;

use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\permission\DefaultPermissions;

final class Rank{
	/** @var string */
	public $name;
	/** @var int */
	public $money;
	/** @var int */
	public $xp;
	/** @var int */
	public $level;
	/** @var int */
	public $hours;

	/**
	 * @param string $name
	 * @param int    $money
	 * @param int    $xp
	 * @param int    $level
	 * @param int    $hours
	 */
	public function __construct(string $name, int $money, int $xp, int $level, int $hours){
		$this->name = $name;
		$this->money = $money * 1000;
		$this->xp = $xp;
		$this->level = $level;
		$this->hours = $hours;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPermission() : string{
		return DefaultPermissions::ROOT . 'rank' . DefaultPermissions::SEPARATOR . $this->name;
	}

	public function getNameFor(SGPlayer $player): string{
		return $player->translate('rank.' . $this->name);
	}

	public function canUpgrade(SGPlayer $player): bool{
		return $player->getXp() >= $this->xp && $player->getMoney() >= $this->money && $player->getCurrentLevel() >= $this->level && $player->getTimePlayedNow() >= ($this->hours * 60 * 60);
	}

	public function getOptions(): array{
		return ["money" => $this->money, "xp" => $this->xp, "level" => $this->level, "hours" => $this->hours];
	}

	public function getPlayerStatue(SGPlayer $player){
		return ["money" => $player->getMoney(), "xp" => $player->getXp(), "level" => $player->getCurrentLevel(), "hours" => floor($player->getTimePlayedNow() / (60 * 60))];
	}
}