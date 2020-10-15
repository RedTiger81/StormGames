<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\message;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Label;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;

class MessagePlayerForm extends CustomForm{

	/** @var string */
	private $target;

	public function __construct(SGPlayer $player, string $name, array $messages){
		$this->target = $name;
		$label = "";
		/** @var \StormGames\SGCore\Message $message */
		foreach($messages as $message){
			$label .= ($message->author === $player->getName() ? TextFormat::DARK_AQUA : TextFormat::DARK_GREEN) . $message->author . ": " . $message->message . " " . $message->dateToString($player) . TextFormat::EOL;
		}
		parent::__construct(TextFormat::YELLOW . $name, [
			new Label('label', $label),
			new Input('message', $player->translate("forms.message.message"))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		/** @var SGPlayer $target */
		$target = $player->getServer()->getPlayerExact($this->target);
		if($target !== null){
			// NOTE : Blank kontrol yapmaya gerek yok
			$player->messages->sendMessage($target, $data->getString('message'));
		}
	}
}