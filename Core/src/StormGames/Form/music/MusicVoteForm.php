<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\music;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\MusicManager;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\NBSDecoder;

class MusicVoteForm extends MenuForm{
	public const MAX_VOTE = 2;

	/** @var int[] */
	private static $votes = [];
	/** @var string[] */
	private static $voted = [];

	public function __construct(SGPlayer $player){
	    if(empty(self::$votes)){
	        foreach(MusicManager::getSounds() as $sound){
	            self::$votes[$sound->getName()] = 0;
            }
        }

		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.music.vote')), '', array_map(function(NBSDecoder $decoder){
			return new MenuOption(TextFormat::YELLOW . $decoder->getName() . TextFormat::EOL . TextFormat::AQUA . self::$votes[$decoder->getName()] . '/' . self::MAX_VOTE);
		}, MusicManager::getSounds()));
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		/** @var NBSDecoder $song */
		$song = array_values(MusicManager::getSounds())[$selectedOption];
		if($player->hasPermission(DefaultPermissions::MUSIC_VOTE_BYPASS)){
			$this->changeSong($song->getName());
			$player->sendMessage(Prefix::MUSIC() . TextFormat::GREEN . $player->translate('forms.music.changed'));
		}else{
			if((self::$voted[$player->getLowerCaseName()] ?? null) === $song->getName()){
				$player->sendMessage(Prefix::MUSIC() . TextFormat::RED . $player->translate('forms.music.voted.error'));
			}else{
				if(++self::$votes[$song->getName()] >= self::MAX_VOTE){
					$this->changeSong($song->getName());
				}else{
					if(isset(self::$voted[$player->getLowerCaseName()])){
						--self::$votes[self::$voted[$player->getLowerCaseName()]];
					}
					self::$voted[$player->getLowerCaseName()] = $song->getName();
				}

				$player->sendMessage(Prefix::MUSIC() . $player->translate('forms.music.voted', [
					TextFormat::GREEN . $song->getName() . TextFormat::GRAY
				]));
			}
		}
	}

	/**
	 * @param string $songName
	 */
	private function changeSong(string $songName) : void{
		self::$voted = [];
		$this->resetSongVotes();
		MusicManager::startNewSong($songName);
	}

	private function resetSongVotes() : void{
	    self::$votes = array_map(function(){ return 0; }, self::$votes);
    }
}