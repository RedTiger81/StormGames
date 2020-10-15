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

namespace Eren5960\Hira;

use pocketmine\block\Sign;
use pocketmine\block\tile\Chest;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\ItemFactory;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ItemTask extends Task{

	public function onRun(int $currentTick){
		foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level){
			foreach($level->getChunks() as $chunk){
				foreach($chunk->getTiles() as $tile){
					if($tile instanceof Chest){
						$array = explode(":", $tile->getName());
						$holder = $tile->getInventory()->getHolder();
						if(isset($array[0]) && $array[0] === "Pazar"){
							/** @var ItemEntity $entity */
							if(($entity = Main::getVirtual($holder)) !== null){
								if(Main::getItemInChest($tile->getInventory(), $entity->getItem(), true) === null){
									Main::clearVirtuals($entity->getPosition());
								}
							}else{
								if(($item = Main::trySetItem($tile, ItemFactory::get((int) $array[1]))) !== null && $item->hasCustomName()){
									/*
									$sign = Main::getSign($tile->getBlock());
									if($sign instanceof Sign){
										$text = clone $sign->getText();
										$line = $text->getLine(1);
										$newLine = str_replace("{item_name}", $item->getCustomName(), Main::get()->getConfig()->get("line-2"));
										if($line !== $newLine){
											$text->setLine(1, $newLine);
											$sign->updateText(, $text);
										}
									}*/
								}
							}
						}
					}
				}
			}
		}
	}
}