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
use StormGames\Form\ModeratorToolsForm;
use StormGames\Prefix;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;

class ModeratorCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'moderator');
		$this->setPermission(DefaultPermissions::MODERATOR);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if(isset($args[0])){
			$value = !$sender->isInvisible();
			$sender->setInvisible($value);
			$sender->setAllowFlight($value);
			$sender->setFlying($value);
			$sender->sendMessage(Prefix::MOD() . ($value ? TextFormat::GREEN . 'ON' : TextFormat::RED . 'OFF'));
			return true;
		}

		$sender->sendForm(new ModeratorToolsForm($sender));

		return true;
	}
}