<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\scheduler\Task;
use StormGames\SGCore\MusicManager;
use StormGames\SGCore\utils\NBSDecoder;

class PlayNoteBlockSoundTask extends Task{

	/** @var NBSDecoder */
	private $song;

	public function __construct(NBSDecoder $decoder){
		$this->song = $decoder;
	}

	public function onRun(int $currentTick) : void{
		if(!MusicManager::isValid()){
			MusicManager::setNoteBlock(null);
			return;
		}
		if(!empty(($players = MusicManager::getPlayers()))){
			$this->song->addTick();
			$noteBlock = MusicManager::getNoteBlock();

			$this->song->play($players, $noteBlock);
			if($this->song->getTick() > $this->song->getLength()){
				MusicManager::startNewSong();
			}
		}
	}

	/**
	 * @return NBSDecoder
	 */
	public function getSong() : NBSDecoder{
		return $this->song;
	}
}