<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use StormGames\Prefix;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\permission\GroupManager;
use StormGames\SGCore\SGPlayer;

class GroupCommand extends Command{

	public function __construct(string $name){
		parent::__construct($name, "Grup ile ilgili işlemleri yapar");

		$this->setPermission(DefaultPermissions::ADMIN);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(empty($args[0])){
			throw new InvalidCommandSyntaxException();
		}

		switch(array_shift($args)){
			case "set":
				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				/** @var SGPlayer $player */
				$player = $sender->getServer()->getPlayer(array_shift($args));
				if($player === null){
					$sender->sendMessage(Prefix::MAIN . "Oyuncu bulunamadı!");
					return true;
				}

				$group = GroupManager::getGroup(array_shift($args));
				if($group === null){
					$sender->sendMessage(Prefix::MAIN . "Grup bulunamadı!");
					return true;
				}

				if(!empty($args)){
                    $group->setTime(strtotime(implode(' ', $args)));
                }
				$player->setGroup($group);
				$sender->sendMessage(Prefix::MAIN . $player->getName() . " oyuncusunun grubu {$group->getName()} ile değiştirildi!");
				break;
		}

		return true;
	}

}