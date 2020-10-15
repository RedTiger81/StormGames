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
use StormGames\Form\PlayerListForm;
use StormGames\SGCore\SGPlayer;

class ListCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'list');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if($sender instanceof SGPlayer){
			$sender->sendForm(new PlayerListForm($sender));
		}else{
			$playerNames = array_map(function(SGPlayer $player){
				return ($player->isModerator() ? TextFormat::AQUA : TextFormat::GREEN) . $player->getName();
			}, $sender->getServer()->getOnlinePlayers());

			$sender->sendMessage(TextFormat::GRAY . "Çevrimiçi " . count($playerNames) . "/" . $sender->getServer()->getMaxPlayers() . TextFormat::EOL . implode(TextFormat::DARK_GRAY . " | ", $playerNames));
		}

		return true;
	}
}