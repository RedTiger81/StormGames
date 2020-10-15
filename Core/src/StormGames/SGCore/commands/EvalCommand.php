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
use pocketmine\Server;

class EvalCommand extends Command{

    public function __construct(string $name){
        parent::__construct($name, 'EVAL');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->isOp()){
            return true;
        }

        if(empty($args)){
            throw new InvalidCommandSyntaxException();
        }

        $echo = false;
        if($args[0] === '-e'){
            $echo = true;
            array_shift($args);
        }

        try{
            $eval = eval(str_replace(['@dir'], [\pocketmine\PATH], implode(' ', $args)));
            $sender->sendMessage('Çalıştı!');
            if($echo){
                $sender->sendMessage($eval);
            }
        }catch(\Throwable $throwable){
            $sender->sendMessage('Hata: ' . $throwable->getMessage());
        }

        return true;
    }

    public function testPermissionSilent(CommandSender $target) : bool{
        return false;
    }
}