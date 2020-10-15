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
 * @date 27 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\generators;


use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;
use pocketmine\world\generator\Generator;

class VoidGenerator extends Generator{
	public function getName() : string{
		return "void";
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		$spawn = $this->getSpawn();
		if($spawn->x >> 4 === $chunkX and $spawn->z >> 4 === $chunkZ){
			$chunk->setFullBlock(0, $spawn->y - 1, 0, BlockFactory::get(BlockLegacyIds::BEDROCK)->getFullId());
		}
		$chunk->setGenerated(true);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		// NOOP
	}

	public function getSpawn() : Vector3{
		return new Vector3(0, 50, 0);
	}
}