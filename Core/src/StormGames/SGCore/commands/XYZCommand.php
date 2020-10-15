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
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use StormGames\SGCore\SGPlayer;

class XYZCommand extends RDCommand{
	/** @var array */
	private $enabled = [];

	public function __construct(string $name){
		parent::__construct($name, 'xyz');
	}

	public function execute(CommandSender $player, string $commandLabel, array $args){
		if($player instanceof SGPlayer){
			$name = $player->getLowerCaseName();
			$pk = new GameRulesChangedPacket();
			$this->enabled[$name] = !($this->enabled[$name] ?? false);
			$pk->gameRules = [
				'showcoordinates' => [1 /*bool*/, $this->enabled[$name]]
			];
			$player->getNetworkSession()->sendDataPacket($pk);
		}

		return true;
	}
}