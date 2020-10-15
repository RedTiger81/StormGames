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
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Label;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class MessageSendForm extends CustomForm{

	/** @var SGPlayer[] */
	private $onlinePlayers = [];

	public function __construct(SGPlayer $player){
	    /** @var SGPlayer $onlinePlayer */
        foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer){
			if($onlinePlayer->getLowerCaseName() !== $player->getLowerCaseName()){
				$this->onlinePlayers[$onlinePlayer->getName()] = $onlinePlayer;
			}
		}

		if(empty($this->onlinePlayers)){
			$elements = [
				new Label('label', TextFormat::RED . $player->translate('error.generic.noPlayer'))
			];
		}else{
			$elements = [
				new Dropdown('targets' ,$player->translate('default.target'), array_keys($this->onlinePlayers)),
				new Input('message', $player->translate("forms.message.message"))
			];
		}

		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('forms.message.newMessage')), $elements);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		if(empty($this->onlinePlayers)) return;

		/** @var SGPlayer $player */
		/** @noinspection PhpUndefinedMethodInspection */
		$name = $this->getElement(0)->getOption($data->getInt('targets'));
		if(($target = $this->onlinePlayers[$name])->isOnline()){
			$player->messages->sendMessage($target, $data->getString('message'));
		}else{
			$player->sendMessage(Prefix::MESSAGE() . TextFormat::RED . $player->translate("error.generic.playerIsOffline", [$name]));
		}
	}
}