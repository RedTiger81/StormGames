<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\world\WorldManager;
use pocketmine\scheduler\Task;
use StormGames\SGCore\SGCore;

class CleanerTask extends Task{
	/** @var WorldManager */
	private $manager;

	public function __construct(WorldManager $manager){
		$this->manager = $manager;
	}

	public function onRun(int $currentTick) : void{
		$count = 0;

		foreach($this->manager->getWorlds() as $level){
			foreach($level->getEntities() as $entity){
				if($entity instanceof ItemEntity or $entity instanceof Arrow){
					$entity->flagForDespawn();
					++$count;
				}
			}
		}

		SGCore::getAPI()->getLogger()->notice($count . ' eşya ve ok temizlendi!');
	}
}