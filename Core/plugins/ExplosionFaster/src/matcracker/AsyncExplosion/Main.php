<?php

/*
 * AsyncExplosion
 *
 * Copyright (C) 2019
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author matcracker
 * @link https://www.github.com/matcracker/AsyncExplosion
 *
*/

declare(strict_types=1);

namespace matcracker\AsyncExplosion;

use Eren5960\entity\Fireball;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function array_filter;
use function array_map;
use function array_merge;

final class Main extends PluginBase implements Listener{
	/**@var QueueTask $queueTask */
	private $queueTask;

	public function onEnable() : void{
		$this->queueTask = new QueueTask();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask($this->queueTask, 20 * 2);
	}

	public function onExplode(EntityExplodeEvent $event) : void{
		if(!$event->getEntity() instanceof Fireball){
			$blockList = $event->getBlockList();
			/**@var Vector3[] $vectors */
			$vectors = array_map(static function(Block $block) : Vector3{
				return $block->getPos()->floor();
			}, $blockList);

			$this->queueTask->addInQueue($vectors, $event->getPosition()->getWorld()->getId());
			$event->setBlockList(array_filter($blockList, static function(Block $block) : bool{//Allow ignites of other TNTs
				return $block->getId() === BlockLegacyIds::TNT;
			}));
		}
	}
}

final class AsyncChunkSet extends AsyncTask{

	/**@var string[] $chunks */
	private $chunks;
	/**@var Vector3[] $vectors */
	private $vectors;
	/**@var int $worldId */
	private $worldId;

	/**
	 * Async constructor.
	 *
	 * @param Vector3[] $vectors
	 * @param int       $worldId
	 */
	public function __construct(array $vectors, int $worldId){
		$this->chunks = self::getSerializedChunks($vectors, $worldId);
		$this->vectors = $vectors;
		$this->worldId = $worldId;
	}

	public function onRun() : void{
		$chunks = (array) $this->chunks;

		foreach($chunks as $hash => $chunkData){
			$chunks[$hash] = FastChunkSerializer::deserialize($chunkData);
		}
		/**@var Chunk[] $chunks */
		foreach($this->vectors as $vector){
			$index = World::chunkHash((int) $vector->getX() >> 4, (int) $vector->getZ() >> 4);
			if(isset($chunks[$index])){
				$chunks[$index]->setFullBlock((int) $vector->getX() & 0x0f, $vector->getY(), (int) $vector->getZ() & 0x0f, 0);
			}
		}
		$this->setResult($chunks);
	}

	public function onCompletion() : void{
		$world = Server::getInstance()->getWorldManager()->getWorld($this->worldId);
		if($world !== null){
			/**@var Chunk[] $chunks */
			$chunks = $this->getResult();
			foreach($chunks as $chunk){
				$world->setChunk($chunk->getX(), $chunk->getZ(), $chunk, false);
			}
		}
	}

	/**
	 * @param Vector3[] $vectors
	 * @param int       $worldId
	 *
	 * @return string[]
	 */
	private static function getSerializedChunks(array $vectors, int $worldId) : array{
		$touchedChunks = [];
		$world = Server::getInstance()->getWorldManager()->getWorld($worldId);
		foreach($vectors as $block){
			$x = $block->getX() >> 4;
			$z = $block->getZ() >> 4;
			$chunk = $world->getChunk($x, $z);
			if($chunk === null){
				continue;
			}
			$touchedChunks[World::chunkHash($x, $z)] = FastChunkSerializer::serialize($chunk);
		}

		return $touchedChunks;
	}
}

final class QueueTask extends Task{

	private $queue = [];

	public function addInQueue(array $vectors, int $worldId) : void{
		if(!isset($this->queue[$worldId])){
			$this->initQueue($worldId);
		}

		$this->queue[$worldId]["vectors"] = array_merge($this->queue[$worldId]["vectors"], $vectors);
	}

	private function initQueue(int $worldId) : void{
		$this->queue[$worldId]["vectors"] = [];
	}

	public function onRun(int $currentTick) : void{
		/**@var int $worldId */
		foreach($this->queue as $worldId => $data){
			Server::getInstance()->getAsyncPool()->submitTask(new AsyncChunkSet($this->queue[$worldId]["vectors"], $worldId));
			unset($this->queue[$worldId]);
		}
	}
}