<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\vote;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\VoteForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class VoteSuccessForm extends MenuForm{

	public function __construct(SGPlayer $player, string $text){
		Utils::broadcastMessage(Prefix::VOTE() . ' #forms.vote.voted.broadcast', [[TextFormat::YELLOW . $player->getName() . TextFormat::GRAY, TextFormat::GOLD . '/vote' . TextFormat::GRAY]]);

		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.success')), $text, [
			new MenuOption($player->translate('forms.back'))
		]);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new VoteForm($player));
	}
}