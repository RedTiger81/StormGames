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

use pocketmine\entity\EntityFactory;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\WorldManager;

class BossManager{
	/** @var null|BossEntity */
	public static $lastBoss = null;

	public static function init(): void{
		EntityFactory::register(BossEntity::class, ["boss"], EntityLegacyIds::IRON_GOLEM);
		EntityFactory::register(CreeterBoss::class, ["creeter"], EntityLegacyIds::CREEPER);
		SGCore::getAPI()->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(self::createBoss()), 20 * 60 * 20, 20 * 60 * 20);
	}

	public static function createBoss(): callable {
		if(self::haveBoss()) return function(){};
		return function(): void{
			return; // TODO
			$world = "arenaWorld";
			/** @var BossEntity $entity */
			$entity = EntityFactory::create([BossEntity::class, CreeterBoss::class][mt_rand(0, 1)], $world, EntityFactory::createBaseNBT($world->getSpawnLocation()));
			$entity->spawnToAll();
			self::$lastBoss = $entity;
			Server::getInstance()->broadcastMessage(" ");
			Server::getInstance()->broadcastMessage("§c ***** §dBOSS §c*****");
			Server::getInstance()->broadcastMessage("§c * " . $entity->getNameV2() . " §carenayı işgal etti!");
			Server::getInstance()->broadcastMessage("§c * §aOna en çok hasar verene ödül vereceğiz arenaya gel hemen.");
			Server::getInstance()->broadcastMessage("§c ***** §dBOSS §c*****");
			Server::getInstance()->broadcastMessage(" ");
		};
	}

	public static function haveBoss(): bool{
		return self::$lastBoss !== null;
	}
}