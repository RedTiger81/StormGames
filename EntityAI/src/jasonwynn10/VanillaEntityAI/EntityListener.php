<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI;

use jasonwynn10\VanillaEntityAI\data\BiomeEntityList;
use jasonwynn10\VanillaEntityAI\entity\AnimalBase;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\MonsterBase;

use jasonwynn10\VanillaEntityAI\entity\passiveaggressive\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\ChunkUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

class EntityListener implements Listener {
	/** @var EntityAI $plugin */
	private $plugin;

	/**
	 * EntityListener constructor.
	 *
	 * @param EntityAI $plugin
	 */
	public function __construct(EntityAI $plugin) {
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
		$this->plugin = $plugin;
		$plugin->getLogger()->debug("Event Listener Registered");
	}

	public static function setHandler(\pocketmine\player\Player $player): void{
		$player->getNetworkSession()->setHandler(new Player($player, $player->getNetworkSession()));
	}
	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onLogin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		EntityAI::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick)use($player): void{ EntityListener::setHandler($player);}), 60);
	}

	public function respawnEvent(PlayerRespawnEvent $event){
		$player = $event->getPlayer();
		EntityAI::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick)use($player): void{ EntityListener::setHandler($player);}), 60);
	}

	/**
	 * @param ChunkLoadEvent $event
	 */
	public function onLoad(ChunkLoadEvent $event) {
		$chunk = $event->getChunk();
		$level = $event->getWorld();
		$packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
		$lightLevel = $level->getFullLightAt($packCenter->x, $packCenter->y, $packCenter->z);
		if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid() and $lightLevel > 8) {
			$biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
			if(array_key_exists($biomeId, BiomeEntityList::BIOME_ANIMALS)) {
				$entityList = BiomeEntityList::BIOME_ANIMALS[$biomeId];
			}else {
				$entityList = BiomeEntityList::BIOME_ANIMALS[$biomeId = 1];
			}
			if(empty($entityList))
				return; // no entities for this biome
			$entityId = $entityList[array_rand(BiomeEntityList::BIOME_ANIMALS[$biomeId])];
			if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
				for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
					$x = mt_rand(-20, 20) + $packCenter->x;
					$z = mt_rand(-20, 20) + $packCenter->z;
					foreach(EntityAI::getEntities() as $class => $arr) {
						if($class instanceof AnimalBase and $class::NETWORK_ID === $entityId) {
							$entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));
							if($entity !== null) {
								$currentPackSize++;
							}
						}
					}
				}
			}
		}elseif(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid() and $lightLevel <= 7) {
			$biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
			if(array_key_exists($biomeId, BiomeEntityList::BIOME_ANIMALS)) {
				$entityList = BiomeEntityList::BIOME_HOSTILE_MOBS[$biomeId];
			}else {
				$entityList = BiomeEntityList::BIOME_HOSTILE_MOBS[$biomeId = 1];
			}
			if(empty($entityList))
				return; // no entities for this biome
			$entityId = $entityList[array_rand(BiomeEntityList::BIOME_HOSTILE_MOBS[$biomeId])];
			if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
				for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
					$x = mt_rand(-20, 20) + $packCenter->x;
					$z = mt_rand(-20, 20) + $packCenter->z;
					foreach(EntityAI::getEntities() as $class => $arr) {
						if($class instanceof MonsterBase and $class::NETWORK_ID === $entityId) {
							$entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));
							if($entity !== null) {
								$currentPackSize++;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param ChunkUnloadEvent $event
	 */
	public function onUnload(ChunkUnloadEvent $event) {
		$chunk = $event->getChunk();
		foreach($chunk->getEntities() as $entity) {
			if($entity instanceof CreatureBase and !$entity->isPersistent()) {
				$entity->flagForDespawn();
			}
		}
	}
}
