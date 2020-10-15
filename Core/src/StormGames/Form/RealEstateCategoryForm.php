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

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\manager\RealEstateManager;
use StormGames\Prefix;
use StormGames\SGCore\utils\Utils;

class RealEstateCategoryForm extends MenuForm{
    /** @var string */
    private $category;

    public function __construct(SGPlayer $player, string $category){
        $this->category = $category;
        parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.realEstate.' . $category)), '', array_map(function(string $name) use($player, $category) : MenuOption{
            return new MenuOption(TextFormat::DARK_GRAY . $player->translate('forms.realEstate.' . $category . '.' . $name) . TextFormat::EOL . TextFormat::GRAY . \StormGames\SGCore\utils\Utils::addMonetaryUnit(RealEstateManager::getProperty($this->category, $name)[1]));
        }, RealEstateManager::getPropertiesByCategory($category)));
    }

    public function onSubmit(Player $player, int $selectedOption) : void{
        /** @var SGPlayer $player */
        $name = RealEstateManager::getPropertiesByCategory($this->category)[$selectedOption];
        if($player->getInventory()->firstEmpty() !== -1){
            if($player->reduceMoney(RealEstateManager::getProperty($this->category, $name)[1])){
                $item = Utils::addBlankEnchantment(ItemFactory::get(ItemIds::MAGMA_CREAM));
                $item->getNamedTag()->setString('estate', $this->category . '.' . $name);
                $item->setCustomName(TextFormat::RESET . TextFormat::DARK_PURPLE . $player->translate('forms.realEstate.' . $this->category . '.' . $name));
                $player->getInventory()->addItem($item);
                $player->sendMessage(Prefix::RealEstate() . $player->translate('forms.realEstate.buy'));
            }else{
                $player->sendMessage(Prefix::RealEstate() . $player->translate('error.generic.noMoney'));
            }
        }else{
            $player->sendMessage(Prefix::RealEstate() . $player->translate('error.generic.fullInventory'));
        }
    }
}