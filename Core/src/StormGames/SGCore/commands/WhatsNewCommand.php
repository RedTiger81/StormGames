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
use pocketmine\form\MenuForm;
use StormGames\SGCore\SGPlayer;

class WhatsNewCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'whatsnew');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof SGPlayer){
			$sender->sendForm(new class($sender->translate("rosedust.whatsnew.title", []), $sender->translateExtended("%rosedust.whatsnew \n\n %whatsnew"), []) extends MenuForm{});
		}
	}
}