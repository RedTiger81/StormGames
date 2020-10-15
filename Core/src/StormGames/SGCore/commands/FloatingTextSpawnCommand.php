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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;
use StormGames\SkyBlock\Main;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class FloatingTextSpawnCommand extends RDCommand{
	public static $dir;

	public function __construct(string $name){
		parent::__construct($name, 'floatingtext');
		self::$dir = SGCore::getAPI()->getDataFolder() . "floating_text/";
		@mkdir(self::$dir);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender->isOp() and !($sender instanceof SGPlayer)){
			return true;
		}

		if(empty($args)){
			throw new InvalidCommandSyntaxException();
		}

		$file = self::$dir . array_shift($args) . '.txt';

		if(is_file($file)){
			Utils::addFloatingText($sender->getPosition(), str_replace("\n", TextFormat::EOL, file_get_contents($file)));
			$sender->sendMessage("§7» §aUçan yazı eklendi.");
		}else{
			$sender->sendMessage("§7» §cDosya bulunamadı.");
		}
		return true;
	}

	public function testPermissionSilent(CommandSender $target) : bool{
		return false;
	}
}