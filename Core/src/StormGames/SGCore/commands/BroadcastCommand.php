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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use StormGames\SGCore\permission\DefaultPermissions;

class BroadcastCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'broadcast', '/b [message]', ['b']);

		$this->setPermission(\pocketmine\permission\DefaultPermissions::ROOT . ".command." . 'broadcast');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(empty($args)){
			throw new InvalidCommandSyntaxException();
		}

		$sender->getServer()->broadcastMessage("§l§dBROADCAST> §r§f" . implode(" ", $args));

		return true;
	}
}