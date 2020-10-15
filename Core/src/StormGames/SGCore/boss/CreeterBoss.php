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

namespace StormGames\SGCore\boss;

use pocketmine\bossbar\BossBar;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\world\Explosion;

class CreeterBoss extends BossEntity{
	public const NETWORK_ID = EntityLegacyIds::CREEPER;
	public $width = 0.2;
	public $height = 0.7;
	public $attackMax = 60;

	public function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setScale(3);
	}

	public function attackEntity(Entity $player){
		if($this->attackDelay > $this->attackMax && $this->location->distanceSquared($player->getPosition()) <= 12){
			$this->attackDelay = 0;

			$explosion = new Explosion($this->location->asPosition(), 4.0, $this);
			$explosion->explodeB();
		}
	}

	public function getName() : string{
		return "creeter";
	}
	public function getNameV2(): string{
		return "§2CREETER";
	}

}