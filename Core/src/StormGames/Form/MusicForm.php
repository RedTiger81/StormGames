<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\music\MusicVoteForm;
use StormGames\Prefix;
use StormGames\SGCore\MusicManager;
use StormGames\SGCore\SGPlayer;

class MusicForm extends MenuForm{

	public function __construct(SGPlayer $player){
		if(!$player->listenMusic){
			$options = [new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.music.on'))];
		}else{
			$options = [
				new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.music.off')),
				new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.music.vote'))
			];
		}

		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.music')), TextFormat::LIGHT_PURPLE . $player->translate('forms.music.text', [TextFormat::GRAY . MusicManager::getCurrentSong()->getName() . TextFormat::LIGHT_PURPLE]), $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		if($selectedOption === 0){
			$player->listenMusic = !$player->listenMusic;
			$player->sendMessage(Prefix::MUSIC() . ($player->listenMusic ?  TextFormat::GREEN . $player->translate('forms.music.on.success') : TextFormat::RED . $player->translate('forms.music.off.success')));
		}else{
			$player->sendForm(new MusicVoteForm($player));
		}
	}
}