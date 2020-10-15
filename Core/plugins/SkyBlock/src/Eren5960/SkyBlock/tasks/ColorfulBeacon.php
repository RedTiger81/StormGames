<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 03 Nisan 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\tasks;
 
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use StormGames\SGCore\tiles\Beacon;

class ColorfulBeacon extends Task{

	public function onRun(int $currentTick){
		$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		foreach($world->getChunks() as $chunk){
			foreach($chunk->getTiles() as $tile){
				if($tile instanceof Beacon){
					$world->setBlock($tile->getPos()->add(0, 1), BlockFactory::get(BlockLegacyIds::STAINED_GLASS, mt_rand(1, 15)));
				}
			}
		}
	}
}