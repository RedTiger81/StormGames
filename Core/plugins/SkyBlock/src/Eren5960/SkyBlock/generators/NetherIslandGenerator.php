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
 * @date 04 Mart 2020
 */
declare(strict_types=1);

namespace Eren5960\SkyBlock\generators;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\world\biome\Biome;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\noise\Simplex;
use pocketmine\world\generator\populator\Populator;

class NetherIslandGenerator extends Generator{
	/** @var Populator[] */
	private $populators = [];
	/** @var Populator[] */
	private $generationPopulators = [];
	/** @var Simplex */
	private $noiseBase;

	/**
	 * @param ChunkManager $world
	 * @param int          $seed
	 * @param mixed[]      $options
	 *
	 * @phpstan-param array<string, mixed> $options
	 *
	 */
	public function __construct(ChunkManager $world, int $seed, array $options = []){
		parent::__construct($world, $seed, $options);

		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 64);
		$this->random->setSeed($this->seed);
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		$chunk = $this->world->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$biome = Biome::getBiome(Biome::HELL);
				$chunk->setBiomeId($x, $z, $biome->getId());
			}
		}

		foreach($this->generationPopulators as $populator){
			$populator->populate($this->world, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach($this->populators as $populator){
			$populator->populate($this->world, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->world, $chunkX, $chunkZ, $this->random);
	}
}
