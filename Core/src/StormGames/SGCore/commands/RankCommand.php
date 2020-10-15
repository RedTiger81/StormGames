<?php
/*
 *  _____               _               ___   ___  __ 
 * /__   \___  _ __ ___| |__   /\/\    / __\ / _ \/__\
 *   / /\/ _ \| '__/ __| '_ \ /    \  / /   / /_)/_\  
 *  / / | (_) | | | (__| | | / /\/\ \/ /___/ ___//__  
 *  \/   \___/|_|  \___|_| |_\/    \/\____/\/   \__/
 *
 * (C) Copyright 2019 TorchMCPE (http://torchmcpe.fun/) and others.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Contributors:
 * - Eren Ahmet Akyol
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\CommandSender;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\RankUpgradeForm;
use StormGames\Rank\Rank;
use StormGames\Rank\RankManager;

class RankCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'rank', null, ["rutbe"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}
		$next = RankManager::getRank($sender->rankId + 1);
		if($next instanceof Rank){
			$sender->sendForm(new RankUpgradeForm($sender, $next));
		}

		return true;
	}
}