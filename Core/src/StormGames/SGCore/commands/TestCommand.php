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
use pocketmine\utils\Color;
use pocketmine\world\particle\DustParticle;
use StormGames\SkyBlock\forms\economy\EconomyOtomaticSellForm;
use StormGames\SkyBlock\manager\boss\BossManager;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;

class TestCommand extends Command{
	public function __construct(string $name){
		parent::__construct($name);

		$this->setPermission(DefaultPermissions::ADMIN);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var SGPlayer $sender */
		if(!$this->testPermission($sender)){
			return true;
		}
		return true;
	}
}