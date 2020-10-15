<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class RemotePlayerCommand extends Command{

    public function __construct(string $name){
        parent::__construct($name, "Bir oyuncuyu yönet");

        $this->setPermission(DefaultPermissions::ADMIN);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
            return true;
        }

        if(empty($args)){
            throw new InvalidCommandSyntaxException();
        }

        $target = $sender->getServer()->getPlayer(implode(" ", $args));
        if($target instanceof SGPlayer){
            $class = SGCore::$formClasses["remotePlayer"];
            $sender->sendForm(new $class($target));
        }else{
            $sender->sendMessage(Prefix::MAIN . TextFormat::RED . "Oyuncu bulunamadı!");
        }

        return true;
    }

}