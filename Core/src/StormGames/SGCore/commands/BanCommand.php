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
use StormGames\SGCore\SGCore;

class BanCommand extends RDCommand{
	public function __construct(string $name){
		parent::__construct($name, 'ban', '/ban [player] [reason] [time] ');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender->isOp()){
			return true;
		}

		if(count($args) < 3){
			throw new InvalidCommandSyntaxException();
		}

		$name = array_shift($args);
		$reason = array_shift($args);
		$time = strtotime(implode(" ", $args));
		if($time === false){
			$sender->sendMessage('Zaman hatalı.');
			return true;
		}

		$db = SGCore::getDatabase();
		if(($db->select(SGCore::TABLE_CRIMINAL_RECORDS, '*', $extra = 'WHERE username=\'' . $name . '\'')->num_rows ?? 0) !== 0){
			$db->query("UPDATE " . SGCore::TABLE_CRIMINAL_RECORDS . " SET banInfo='1:$reason:$time:{$sender->getName()}', banCount=banCount+1 $extra");
			if(($player = $sender->getServer()->getPlayerExact($name)) !== null){
				$player->disconnect('', 'YOU ARE BANNED!');
			}
			$sender->sendMessage('BANNED!');
		}else{
			$sender->sendMessage('Oyuncu bulunamadı.');
		}

		return true;
	}
}