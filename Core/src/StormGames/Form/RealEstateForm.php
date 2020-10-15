<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\RealEstateManager;
use StormGames\Prefix;

class RealEstateForm extends MenuForm{

    public function __construct(SGPlayer $player){
        parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::BLUE . $player->translate('forms.realEstate')), '', array_map(function(string $category) use($player): MenuOption{
            return new MenuOption($player->translate('forms.realEstate.' . $category));
        }, RealEstateManager::getCategories()));
    }

    public function onSubmit(Player $player, int $selectedOption) : void{
        /** @var SGPlayer $player */
        $player->sendForm(new RealEstateCategoryForm($player, RealEstateManager::getCategories()[$selectedOption]));
    }
}