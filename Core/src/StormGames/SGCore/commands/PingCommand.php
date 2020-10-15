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
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class PingCommand extends RDCommand{

    public function __construct(string $name){
        parent::__construct($name, 'ping');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
            return true;
        }

        $ping = $sender->getNetworkSession()->getPing();
	    $color = TextFormat::GOLD;
        if($ping < 100){
            $color = TextFormat::GREEN;
        }elseif($ping >= 200){
            $color = TextFormat::RED;
        }
        $sender->sendMessage(Prefix::MAIN . $sender->translate("commands.ping.yourPing", [
            $color . $ping . 'ms' . TextFormat::GRAY
        ]));

        return true;
    }
}