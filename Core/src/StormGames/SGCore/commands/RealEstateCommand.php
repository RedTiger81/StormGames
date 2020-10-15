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
use StormGames\Form\RealEstateForm;
use StormGames\SGCore\commands\RDCommand;

class RealEstateCommand extends RDCommand{
    public function __construct(string $name){
        parent::__construct($name, 'realEstate', '/re', ['re', 'emlak']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
            return true;
        }

        $sender->sendForm(new RealEstateForm($sender));

        return true;
    }
}