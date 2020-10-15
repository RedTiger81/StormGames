<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class StopServerTask extends Task{

	/** @var Server */
	private $server;
	/** @var int */
	private $time = 11;

	public function __construct(Server $server){
		$this->server = $server;
	}

	public function onRun(int $currentTick) : void{
		if(--$this->time <= 0){
			$this->server->getLogger()->warning("Sunucu yeniden başlatılıyor!");
			$this->server->dispatchCommand(SGCore::getAPI()->console, 'stop');
		}else{
			/** @var SGPlayer $player */
			foreach($this->server->getOnlinePlayers() as $player){
				$player->sendTip(TextFormat::RED . $player->translate("tasks.stopServer.countdown", [TextFormat::YELLOW . $this->time . TextFormat::RED]));
			}
		}
	}
}