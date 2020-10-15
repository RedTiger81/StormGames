<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use pocketmine\entity\Entity;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\World;
use StormGames\SGCore\task\PlayNoteBlockSoundTask;
use StormGames\SGCore\tiles\NoteBlock;
use StormGames\SGCore\utils\NBSDecoder;

class MusicManager{

	/** @var NBSDecoder[] */
	private static $sounds = [];
	/** @var SGCore */
	private static $core;
	/** @var TaskHandler */
	private static $currentTask;
	/** @var NoteBlock|null */
	private static $noteBlock;


	public static function init(SGCore $core) : void{
		self::$core = $core;

		foreach(glob($core->getResourcesDir() . "nbsounds/*.nbs") as $sound){
			$decode = new NBSDecoder($sound);
			self::$sounds[$decode->getName()] = $decode;
		}

		self::startNewSong();
	}

	/**
	 * Starting new song
	 * @param string|null $sound
	 */
	public static function startNewSong(string $sound = null) : void{
		if(self::$currentTask !== null){
			self::$currentTask->cancel();
			self::getCurrentSong()->reset();
			self::$currentTask = null;
		}

		$randomSong = self::$sounds[$sound ?? array_rand(self::$sounds)];
		self::$currentTask = self::$core->getScheduler()->scheduleRepeatingTask(new PlayNoteBlockSoundTask($randomSong), intval(2990 / $randomSong->getTempo()));
	}

	/**
	 * @param NoteBlock|null $noteBlock
	 */
	public static function setNoteBlock(?NoteBlock $noteBlock) : void{
		self::$noteBlock = $noteBlock;
	}

	/**
	 * @return NoteBlock|null
	 */
	public static function getNoteBlock() : ?NoteBlock{
		return self::$noteBlock;
	}

	/**
	 * @return NBSDecoder[]
	 */
	public static function getSounds() : array{
		return self::$sounds;
	}

	/**
	 * @return SGPlayer[]
	 */
	public static function getPlayers() : array{
		return array_filter(self::$noteBlock->getPos()->getWorld()->getPlayers(), function(Entity $player){
			return $player instanceof SGPlayer and $player->listenMusic and $player->getPosition()->distanceSquared(self::$noteBlock->getPos()) < 4000;
		});
	}

	/**
	 * Returns current song
	 * @return NBSDecoder
	 */
	public static function getCurrentSong() : NBSDecoder{
		/** @noinspection PhpUndefinedMethodInspection */
		return self::$currentTask->getTask()->getSong();
	}

	/**
	 * Note block is not null
	 * @return bool
	 */
	public static function isValid() : bool{
		return self::$noteBlock !== null && self::$noteBlock->getPos()->getWorld() instanceof World;
	}
}