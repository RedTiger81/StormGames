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
use StormGames\SGCore\SGPlayer;
use StormGames\Form\VIPForm;
use StormGames\SGCore\commands\RDCommand;
use StormGames\SGCore\permission\DefaultPermissions;

class VIPCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'vip');

		$this->setPermission(DefaultPermissions::VIP);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		$sender->sendForm(new VIPForm($sender));

		return true;
	}
}