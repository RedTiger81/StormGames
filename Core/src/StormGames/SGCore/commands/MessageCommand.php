<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use StormGames\Form\MessageForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class MessageCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'message', null, ["mesaj"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if(count($args) >= 2){
			/** @var SGPlayer $target */
			$name = array_shift($args);
			$target = $sender->getServer()->getPlayer($name);
			if($target === null){
				$sender->sendMessage(Prefix::MESSAGE() . TextFormat::RED . $sender->translate('error.generic.playerNotFound', [$name]));
			}elseif(empty($args)){
				$sender->sendMessage(Prefix::MESSAGE() . TextFormat::RED . $sender->translate("forms.message.notBlank"));
			}else{
				$sender->messages->sendMessage($target, implode(' ', $args));
			}
		}else{
			$sender->sendForm(new MessageForm($sender));
		}

		return true;
	}
}