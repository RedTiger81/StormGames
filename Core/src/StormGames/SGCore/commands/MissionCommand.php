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
use StormGames\Form\mission\MissionInfoForm;
use StormGames\Form\MissionsForm;
use StormGames\SGCore\commands\RDCommand;

class MissionCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'mission', null, ["görev"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if($sender->getCurrentMission() !== null){
			$sender->sendForm(new MissionInfoForm($sender, $sender->getCurrentMission()::getId()));
		}else{
			$sender->sendForm(new MissionsForm($sender));
		}

		return true;
	}
}