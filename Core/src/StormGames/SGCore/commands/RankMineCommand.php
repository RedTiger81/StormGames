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
use pocketmine\world\Position;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\manager\AreaManager;
use StormGames\SGCore\commands\RDCommand;
use StormGames\SGCore\permission\DefaultPermissions;

class RankMineCommand extends RDCommand{
	private static $inSetup = [];

	public function __construct(string $name){
		parent::__construct($name, 'rankMine');

		$this->setPermission(DefaultPermissions::ADMIN);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if(isset(self::$inSetup[$sender->getLowerCaseName()])){
			if(isset(self::$inSetup[$sender->getLowerCaseName()][1])){
				if(isset($args[0])){
					self::$inSetup[$sender->getLowerCaseName()][2] = 'stormgames.rank.' . $args[0];
					AreaManager::addToArea(self::$inSetup[$sender->getLowerCaseName()]);
					unset(self::$inSetup[$sender->getLowerCaseName()]);
					$sender->sendMessage('§8» §eArea ayarlandı!');
				}else{
					$sender->sendMessage('§8» §cİzin Girin!');
				}
			}else{
				self::$inSetup[$sender->getLowerCaseName()][1] = Position::fromObject($sender->getPosition()->floor(), $sender->getWorld());
				$sender->sendMessage('§8» §aPos 2 ayarlandı!');
			}
		}else{
			self::$inSetup[$sender->getLowerCaseName()][0] = Position::fromObject($sender->getPosition()->floor(), $sender->getWorld());
			$sender->sendMessage('§8» §aPos 1 ayarlandı!');
		}

		return true;
	}
}