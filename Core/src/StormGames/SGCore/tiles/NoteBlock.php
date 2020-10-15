<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\tiles;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\Spawnable;
use StormGames\SGCore\MusicManager;

class NoteBlock extends Spawnable{
	public const TAG_NOTE = 'note';
	public const TAG_POWERED = 'powered';

	/** @var int */
	protected $note = 0;
	/** @var bool */
	protected $powered = false;

	public function readSaveData(CompoundTag $nbt) : void{
		if(MusicManager::getNoteBlock() === null and $this->pos->getWorld()->getId() === $this->pos->getWorld()->getServer()->getWorldManager()->getDefaultWorld()->getId()){
			MusicManager::setNoteBlock($this);
		}

		$this->note = $nbt->getTag(self::TAG_NOTE)->getValue() ?? 0;
		$this->powered = $nbt->getByte(self::TAG_POWERED, 0) !== 0;
	}

	public function setNote(int $note) : void{
		$this->note = $note;
	}

	public function getNote() : int{
		return $this->note;
	}

	public function setPowered(bool $value) : void{
		$this->powered = $value;
	}

	public function isPowered() : bool{
		return $this->powered;
	}

	public function getDefaultName() : string{
		return "NoteBlock";
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_NOTE, $this->note);
		$nbt->setByte(self::TAG_POWERED, intval($this->powered));
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_NOTE, $this->note);
		$nbt->setByte(self::TAG_POWERED, intval($this->powered));
	}
}