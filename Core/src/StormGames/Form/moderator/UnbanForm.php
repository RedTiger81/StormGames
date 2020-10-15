<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\moderator;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use StormGames\Form\ModeratorToolsForm;
use StormGames\Prefix;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class UnbanForm extends CustomForm{
	public function __construct(SGPlayer $player, string $title){
		parent::__construct($title, [
			new Input('player', $player->translate('forms.moderatortools.player'))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$name = strtolower($data->getString('player'));

		$db = SGCore::getDatabase();
		$result = $db->select(SGCore::TABLE_CRIMINAL_RECORDS, '*', $extra = "WHERE username='$name'");
		if(($result->num_rows ?? 0) !== 0){
			$db->query('UPDATE ' . SGCore::TABLE_CRIMINAL_RECORDS . ' SET banInfo=\'0:null:0:null\' ' . $extra);
			$player->sendMessage(Prefix::MOD() . 'UNBANNED!');
		}else{
			$player->sendMessage(Prefix::MOD() . $player->translate('error.generic.playerNotFound', [$name]));
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new ModeratorToolsForm($player));
	}
}