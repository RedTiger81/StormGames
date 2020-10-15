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
use StormGames\Prefix;
use StormGames\SGCore\commands\RDCommand;

class TutorialCommand extends RDCommand{

    public function __construct(string $name){
        parent::__construct($name, 'tutorial', null, ["öğretici", "bilgi"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
            return true;
        }

        if($sender->getMode() !== SGPlayer::MODE_TUTORIAL){
            $sender->setTutorialMode(true);
        }else{
            $sender->sendMessage(Prefix::MAIN . TextFormat::RED . $sender->translate('tutorial.error'));
        }

        return true;
    }
}