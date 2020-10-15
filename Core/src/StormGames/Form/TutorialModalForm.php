<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\ModalForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Helper\HelperManager;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class TutorialModalForm extends ModalForm{

    public function __construct(SGPlayer $player){
        parent::__construct(
            sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('tutorial')),
            $player->translate('tutorial.modal'),
            $player->translate("forms.yes"),
            $player->translate("forms.no")
        );
    }

    public function onSubmit(Player $player, bool $choice) : void{
    	/** @var SGPlayer $player */
        if($choice){
            $player->getServer()->dispatchCommand($player, 'tutorial');
        }else{
	        HelperManager::addAll($player);
        }
    }
}