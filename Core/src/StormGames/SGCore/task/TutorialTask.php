<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Helper\HelperManager;
use StormGames\SGCore\utils\Utils;

class TutorialTask extends Task{

	private const PARTS = [
		['message' => 'tutorial.welcome'],
		['message' => 'tutorial.help', 'keys' => ['/help']],
		['message' => 'tutorial.anvil', 'keys' => ['/a']],
		['message' => 'tutorial.economy', 'keys' => ['/e']],
		['message' => 'tutorial.warp', 'keys' => ['/w']],
		['message' => 'tutorial.mme', 'keys' => ['/çırak']],
		['message' => 'tutorial.otocs', 'keys' => ['/otocs']],
		['message' => 'tutorial.mob', 'keys' => ['/mob']],
		['level' => 'arena', 'message' => 'tutorial.arena', 'keys' => ['/w a']],
		['level' => 'lobby', 'message' => 'tutorial.lobby', 'keys' => ['/w l']]
	];

	/** @var SGPlayer */
	private $player;
	/** @var int */
	private $part = -1;

	public function __construct(SGPlayer $player){
		$this->player = $player;
		$this->player->setGamemode(GameMode::SPECTATOR());
		$this->player->teleport($this->player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
	}

	public function onRun(int $currentTick){
		if($this->player->isOnline()){
			$part = self::PARTS[++$this->part] ?? null;
			if($part === null){
				$this->finish();
			}else{
				if(isset($part['level'])){
					$this->player->teleport(Utils::getWorldByName($part['level'])->getSpawnLocation());
				}
				$keys = $part['keys'] ?? [];
				$this->player->sendTitle(
					TextFormat::DARK_AQUA . $this->player->translate($part['message'], $keys),
					TextFormat::AQUA . $this->player->translate($part['message'] . '.desc', $keys)
				);
			}
		}else{
			$this->finishTask();
		}
	}

	private function finish() : void{
		$this->player->setGamemode(GameMode::ADVENTURE());
		$this->player->teleport($this->player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
		$this->player->setTutorialMode(false);
		HelperManager::addAll($this->player);
		$this->finishTask();
	}

	private function finishTask() : void{
		$this->getHandler()->cancel();
	}
}