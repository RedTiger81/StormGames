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
use StormGames\Form\menu\PromotionForm;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\PromotionManager;
use StormGames\SGCore\SGPlayer;

class PromotionCommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'promotion', null, ["promosyon"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof SGPlayer and (empty($args) or !$sender->hasPermission(DefaultPermissions::ADMIN))){
			$sender->sendForm(new PromotionForm($sender));
			return true;
		}

		if($args[0] == 'olustur'){
			if(count($args) < 2){
				$sender->sendMessage("/" . $this->getName() . " create <random:code> [coins] [usableCount]");
				return true;
			}

			$coins = intval($args[2] ?? 5);
			$usableCount = intval($args[3] ?? 20);
			if($args[1] == 'random'){
				$code = PromotionManager::createCode($coins, $usableCount);
			}else{
				if(!PromotionManager::addCode($args[1], $coins, $usableCount)){
					$sender->sendMessage("§8» §cBöyle bir kod zaten var!");
					return true;
				}
				$code = $args[1];
			}

			$sender->sendMessage("§8» §f$code §6kodu oluşturuldu. Ödül: §f$coins §6Sikke");
		}elseif($args[0] == 'sil'){
			if(count($args) < 2){
				$sender->sendMessage("/" . $this->getName() . " remove <code>");
				return true;
			}

			if(PromotionManager::removeCode($args[1])){
				$sender->sendMessage("§8» §f$args[1] §ekodu kaldırıldı!");
			}else{
				$sender->sendMessage("§8» §f$args[1] §ekodu bulunamadı!");
			}
		}

		return true;
	}
}