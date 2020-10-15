<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\task;

use jasonwynn10\VanillaEntityAI\entity\AnimalBase;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;

use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use pocketmine\world\World as Level;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class DespawnTask extends Task {
	public function onRun(int $currentTick) {
		foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level) {
			/** @var Chunk[] $chunks */
			$chunks = [];
			foreach($level->getChunks() as $chunk) {
				if($chunk->isPopulated()) {
					$hash = Level::chunkHash($chunk->getX(), $chunk->getZ());
					Level::getXZ($hash, $chunkX, $chunkZ);
					$chunks[$hash] = $level->getChunk($chunkX, $chunkZ, true);
				}
			}
			foreach($chunks as $chunk) {
				if(mt_rand(1, 50) !== 1) {
					continue;
				}
				foreach($chunk->getEntities() as $entity) {
					$distanceCheck = true;
					foreach($entity->getViewers() as $player) {
						if($entity->distance($player) < 54) {
							$distanceCheck = false;
							break;
						}
					}
					// TODO: check age
					if($entity instanceof MonsterBase and $distanceCheck and $entity->getLevel()->getFullLight($entity->floor()) > 8 and !$entity->isPersistent()) {
						$entity->flagForDespawn();
					}elseif($entity instanceof AnimalBase and $distanceCheck and $entity->getLevel()->getFullLight($entity->floor()) < 7 and !$entity->isPersistent()) {
						$entity->flagForDespawn();
					}
				}
			}
		}
	}
}