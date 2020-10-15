<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Server;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\tiles\NoteBlock;

class NBSDecoder extends BinaryStream{

	public const INSTRUMENT_PIANO = 0;
	public const INSTRUMENT_BASS = 1;
	public const INSTRUMENT_BASS_DRUM = 2;
	public const INSTRUMENT_CLICK = 3;
	public const INSTRUMENT_TABOUR = 4;

	public const INSTRUMENT_TO_TYPE = [
		self::INSTRUMENT_PIANO => 0,
		self::INSTRUMENT_BASS => 4,
		self::INSTRUMENT_BASS_DRUM => 1,
		self::INSTRUMENT_CLICK => 2,
		self::INSTRUMENT_TABOUR => 3
	];

	/** @var string */
	private $name;
	/** @var int */
	private $length;
	/** @var int */
	private $tempo;
	/** @var array */
	private $sounds;

	/** @var int */
	private $tick = 0;

	public function __construct(string $path){
		assert(is_file($path));
		$fopen = fopen($path, "r");
		parent::__construct(fread($fopen, filesize($path)), 0);
		fclose($fopen);

		$this->handleNoteBlock($this->handleHeader());
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getLength() : int{
		return $this->length;
	}

	/**
	 * @return int
	 */
	public function getTempo() : int{
		return $this->tempo;
	}

	/**
	 * @return array
	 */
	public function getSounds() : array{
		return $this->sounds;
	}

	/**
	 * Play for players
	 *
	 * @param SGPlayer[] $players
	 * @param NoteBlock $block
	 * @return bool
	 */
	public function play(array $players, NoteBlock $block) : bool{
		if(!isset($this->sounds[$this->tick])){
			return true;
		}

		foreach($players as $player){
			if($this->tick < 10 or (time() - $player->joinTime) < 10){
				$player->sendTip(TextFormat::LIGHT_PURPLE . "♪ " . TextFormat::GOLD . $player->translate("music.now.playing", [TextFormat::WHITE . $this->name]) . TextFormat::LIGHT_PURPLE . " ♪");
			}
		}

		foreach($this->sounds[$this->tick] as $sound){
			$blockEvent = new BlockEventPacket();
			$blockEvent->x = $block->getPos()->x;
			$blockEvent->y = $block->getPos()->y;
			$blockEvent->z = $block->getPos()->z;
			$blockEvent->eventType = $sound[1];
			$blockEvent->eventData = $sound[0];

			$levelSound = new LevelSoundEventPacket();
			$levelSound->sound = LevelSoundEventPacket::SOUND_NOTE;
			$levelSound->extraData = $sound[0];
			$levelSound->position = $block->getPos();
			$levelSound->disableRelativeVolume = true;
			Server::getInstance()->broadcastPackets($players, [$blockEvent, $levelSound]);
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getTick() : int{
		return $this->tick;
	}

	/**
	 * Adds 1 to ticks
	 */
	public function addTick() : void{
		$this->tick++;
	}

	/**
	 * Reset
	 */
	public function reset() : void{
		$this->tick = 0;
	}

	/**
	 * Returns height
	 * The last layer with at least one note block in it, or the last layer that have had its name or volume changed.
	 *
	 * @return int
	 */
	public function handleHeader() : int{
		$this->length = $this->getShort();
		$height = $this->getShort();
		$this->name = $this->getString();
		$this->getString(); // author
		$this->getString(); // original author
		$this->getString(); // description
		$this->tempo = $this->getShort(); // The tempo of the song multiplied by 100 (1225 => 12.25). [TPS]
		$this->getByte(); // auto-saving
		$this->getByte(); // auto-saving duration [1-60]
		$this->getByte(); // Time signature: If this is 3, then the signature is 3/4. Default is 4. This value ranges from 2-8.
		$this->getInt(); // Minutes spent : The amount of minutes spent on the project.
		$this->getInt(); // left click
		$this->getInt(); // right click
		$this->getInt(); // blocks added
		$this->getInt(); // blocks removed
		$this->getString(); // MIDI/Schematic file name

		return $height;
	}

	public function handleNoteBlock(int $height){
		$tick = $this->getShort() - 1;
		while(true){
			$sounds = [];

			$this->getShort();
			while(true){
				$type = $this->getType();
				$sounds[] = [$this->getByte() - ($height < 10 ? 33 : 48) + $height, $type];
				if($this->getShort() === 0) break;
			}
			$this->sounds[$tick] = $sounds;

			if(($jump = $this->getShort()) === 0) break;
			$tick += $jump;
		}
	}

	public function getType() : int{
		return self::INSTRUMENT_TO_TYPE[$this->getByte()] ?? self::INSTRUMENT_PIANO;
	}

	public function getShort() : int{
		return unpack("S", $this->get(2))[1];
	}

	public function getString() : string{
		return $this->get(unpack("I", $this->get(4))[1]);
	}
}