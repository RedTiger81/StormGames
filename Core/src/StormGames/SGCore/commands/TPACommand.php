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
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\TPAForm;
use StormGames\Form\TPAModalForm;
use StormGames\Prefix;
use StormGames\SGCore\commands\RDCommand;

class TPACommand extends RDCommand{

	public function __construct(string $name){
		parent::__construct($name, 'tpa');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if(!empty($args)){
			$name = implode(' ', $args);
			/** @var SGPlayer $target */
			$target = $sender->getServer()->getPlayer($name);
			if($target !== null){
				$name = $target->getName();
				if(TPAForm::isDenied($target)){
					$sender->sendMessage(Prefix::TPA() . TextFormat::RED . $sender->translate('forms.tpa.off.target', [$name]));
				}else{
					$target->sendForm(new TPAModalForm($target, $sender));
					$sender->sendMessage(Prefix::TPA() . TextFormat::GREEN . $sender->translate('forms.tpa.sent', [$name]));
				}
			}else{
				$sender->sendMessage(Prefix::TPA() . TextFormat::RED . $sender->translate('error.generic.playerNotFound', [$name]));
			}
		}else{
			$sender->sendForm(new TPAForm($sender));
		}

		return true;
	}
}