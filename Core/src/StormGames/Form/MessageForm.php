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
use StormGames\Form\message\MessagePlayerForm;
use StormGames\Form\message\MessageSendForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class MessageForm extends MenuForm{

	/** @var array */
	private $messages;

	public function __construct(SGPlayer $player){
		$this->messages = $player->messages->getMenuOptions();
		array_unshift($this->messages, new MenuOption($player->translate("forms.message.newMessage")));
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('forms.message.title')), '', $this->messages);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		if($selectedOption === 0){
			$player->sendForm(new MessageSendForm($player));
		}else{
			$sender = array_keys($this->messages)[$selectedOption];
			$player->sendForm(new MessagePlayerForm($player, $sender, $player->messages->getMessages($sender)));
		}
	}
}