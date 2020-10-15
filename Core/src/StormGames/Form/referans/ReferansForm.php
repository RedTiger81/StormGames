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

namespace StormGames\Form\referans;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\TutorialModalForm;
use StormGames\SGCore\Main;
use StormGames\Prefix;
use StormGames\SGCore\utils\MySQLValidator;

class ReferansForm extends CustomForm{
	public const REWARD = 2500;
	public $force = true;

	public function __construct(SGPlayer $player, $extraTitle = ""){
		parent::__construct(sprintf(Prefix::FORM_TITLE, "Referans") . $extraTitle, [
			new Input("sender", "\n" . TextFormat::AQUA . $player->translate("referans.form.text"))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$sender = strtolower($data->getString("sender"));
		$sender_online = Server::getInstance()->getPlayerExact($sender);
		if($sender_online instanceof SGPlayer && $sender_online->isOnline() && $sender_online->spawned){
			$sender_online->addMoney(self::REWARD);
			$player->addMoney(1000);
			$player->sendMessage(TextFormat::GREEN . $player->translate("referans.form.success"));
			$this->force = true;
		}elseif(Server::getInstance()->getOfflinePlayerData($sender) !== null){
			try{
				MySQLValidator::get(SGCore::TABLE_PLAYERS)->update(SGCore::getDatabase(), ["money" => MySQLValidator::get(SGCore::TABLE_PLAYERS)->select(SGCore::getDatabase(), 'WHERE username=\'' . $sender . '\'')["money"] + self::REWARD], 'WHERE username=\'' . $sender . '\'');
			}catch(\Exception $e){}
			$player->addMoney(1000);
			$player->sendMessage(TextFormat::GREEN . $player->translate("referans.form.success"));
			$this->force = true;
		}else{
			 $this->force = false;
			 $player->sendForm(new self($player, "§c - " . $player->translate("referans.form.error")));
		}
	}

	public function onClose(Player $player) : void{
		if($this->force)
			$player->sendForm(new TutorialModalForm($player));
	}
}