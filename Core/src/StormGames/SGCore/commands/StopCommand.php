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
use pocketmine\utils\TextFormat;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;

class StopCommand extends Command{

    public function __construct(string $name){
        parent::__construct($name, 'Sunucuyu kapatır');

        $this->setPermission(DefaultPermissions::ADMIN);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        $server = $sender->getServer();
        /** @var SGPlayer $player */
        foreach($server->getOnlinePlayers() as $player){
            $player->disconnect('', TextFormat::RED . $player->translate('tasks.stopServer.restarting'));
        }
        $server->shutdown();

        return true;
    }
}