<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\blocks;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use StormGames\SGCore\MusicManager;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\tiles\NoteBlock as TileNoteBlock;

class NoteBlock extends Opaque {
	public const INSTRUMENT_PIANO = 0;
	public const INSTRUMENT_BASS_DRUM = 1;
	public const INSTRUMENT_CLICK = 2;
	public const INSTRUMENT_TABOUR = 3;
	public const INSTRUMENT_BASS = 4;

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();

		/** @var TileNoteBlock $tile */
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if(MusicManager::getNoteBlock() === null and $this->pos->getWorld()->getId() === Server::getInstance()->getWorldManager()->getDefaultWorld()->getId()){
			MusicManager::setNoteBlock($tile);
		}
	}

	/**
	 * @return int
	 */
	public function calculateNote() : int{
		/** @var TileNoteBlock $tile */
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileNoteBlock){
			$note = $tile->getNote();
			$nextNote = $note + 1;
			if($nextNote > 24) $nextNote = 0;

			$tile->setNote($nextNote);
			return $note;
		}

		return 0;
	}
	/**
	 * @return int
	 */
	public function getInstrument() : int{
		switch($this->getSide(Facing::DOWN)->getId()){
			case BlockLegacyIds::WOOD:
			case BlockLegacyIds::LOG:
			case BlockLegacyIds::LOG2:
			case BlockLegacyIds::PLANKS:
			case BlockLegacyIds::WOODEN_SLAB:
			case BlockLegacyIds::DOUBLE_WOODEN_SLAB:
			case BlockLegacyIds::OAK_STAIRS:
			case BlockLegacyIds::SPRUCE_STAIRS:
			case BlockLegacyIds::BIRCH_STAIRS:
			case BlockLegacyIds::JUNGLE_STAIRS:
			case BlockLegacyIds::ACACIA_STAIRS:
			case BlockLegacyIds::DARK_OAK_STAIRS:
			case BlockLegacyIds::FENCE:
			case BlockLegacyIds::FENCE_GATE:
			case BlockLegacyIds::SPRUCE_FENCE_GATE:
			case BlockLegacyIds::BIRCH_FENCE_GATE:
			case BlockLegacyIds::JUNGLE_FENCE_GATE:
			case BlockLegacyIds::DARK_OAK_FENCE_GATE:
			case BlockLegacyIds::ACACIA_FENCE_GATE:
			case BlockLegacyIds::BOOKSHELF:
			case BlockLegacyIds::CHEST:
			case BlockLegacyIds::CRAFTING_TABLE:
			case BlockLegacyIds::SIGN_POST:
			case BlockLegacyIds::WALL_SIGN:
			case BlockLegacyIds::OAK_DOOR_BLOCK:
			case BlockLegacyIds::SPRUCE_DOOR_BLOCK:
			case BlockLegacyIds::BIRCH_DOOR_BLOCK:
			case BlockLegacyIds::JUNGLE_DOOR_BLOCK:
			case BlockLegacyIds::ACACIA_DOOR_BLOCK:
			case BlockLegacyIds::DARK_OAK_DOOR_BLOCK:
			case BlockLegacyIds::NOTEBLOCK:
				return self::INSTRUMENT_BASS;
			case BlockLegacyIds::SAND:
			case BlockLegacyIds::SOUL_SAND:
				return self::INSTRUMENT_TABOUR;
			case BlockLegacyIds::GLASS:
			case BlockLegacyIds::GLASS_PANE:
				return self::INSTRUMENT_CLICK;
			case BlockLegacyIds::STONE:
			case BlockLegacyIds::COBBLESTONE:
			case BlockLegacyIds::SANDSTONE:
			case BlockLegacyIds::MOSS_STONE:
			case BlockLegacyIds::BRICK_BLOCK:
			case BlockLegacyIds::STONE_BRICK:
			case BlockLegacyIds::NETHER_BRICK_BLOCK:
			case BlockLegacyIds::QUARTZ_BLOCK:
			case BlockLegacyIds::STONE_SLAB:
			case BlockLegacyIds::COBBLESTONE_STAIRS:
			case BlockLegacyIds::BRICK_STAIRS:
			case BlockLegacyIds::STONE_BRICK_STAIRS:
			case BlockLegacyIds::NETHER_BRICK_STAIRS:
			case BlockLegacyIds::SANDSTONE_STAIRS:
			case BlockLegacyIds::QUARTZ_STAIRS:
			case BlockLegacyIds::COBBLESTONE_WALL:
			case BlockLegacyIds::NETHER_BRICK_FENCE:
			case BlockLegacyIds::BEDROCK:
			case BlockLegacyIds::GOLD_ORE:
			case BlockLegacyIds::IRON_ORE:
			case BlockLegacyIds::COAL_ORE:
			case BlockLegacyIds::LAPIS_ORE:
			case BlockLegacyIds::DIAMOND_ORE:
			case BlockLegacyIds::REDSTONE_ORE:
			case BlockLegacyIds::GLOWING_REDSTONE_ORE:
			case BlockLegacyIds::EMERALD_ORE:
			case BlockLegacyIds::FURNACE:
			case BlockLegacyIds::BURNING_FURNACE:
			case BlockLegacyIds::OBSIDIAN:
			case BlockLegacyIds::MONSTER_SPAWNER:
			case BlockLegacyIds::NETHERRACK:
			case BlockLegacyIds::ENCHANTING_TABLE:
			case BlockLegacyIds::END_STONE:
			case BlockLegacyIds::TERRACOTTA:
			case BlockLegacyIds::COAL_BLOCK:
				return self::INSTRUMENT_BASS_DRUM;
			default:
				return self::INSTRUMENT_PIANO;
		}
	}

	/**
	 * @param Item $item
	 * @param int $face
	 * @param Vector3 $clickVector
	 * @param SGPlayer|Player|null $player
	 * @return bool
	 */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$up = $this->getSide(Facing::UP);
		if(!$player->inDefaultLevel() and $up->getId() === 0 and $this instanceof NoteBlock){
			$instrument = $this->getInstrument();
			$pitch = $this->calculateNote();

			$pk = new BlockEventPacket();
			$pk->x = $this->pos->x;
			$pk->y = $this->pos->y;
			$pk->z = $this->pos->z;
			$pk->eventType = $instrument;
			$pk->eventData = $pitch;
			$player->getNetworkSession()->sendDataPacket($pk);

			$pk = new LevelSoundEventPacket();
			$pk->sound = LevelSoundEventPacket::SOUND_NOTE;
			$pk->extraData = $instrument;
			$pk->position = $this;
			$player->getNetworkSession()->sendDataPacket($pk);
			return true;
		}

		return false;
	}

	public function getFuelTime() : int{
		return 300;
	}
}