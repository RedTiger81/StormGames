<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\economy;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;

class EconomyShopCategoryForm extends MenuForm{

	/** @var string */
	private $category;

	public function __construct(SGPlayer $player, string $categoryName){
		$this->category = $categoryName;
		$options = [];
		foreach(EconomyShopForm::getShop()[$categoryName] as $key => $value){
			$options[] = new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.economy.shop.category.' . $categoryName . '.' . $key), isset($value['icon_url']) ? new FormIcon($value['icon_url']) : null);
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.economy.shop.category.' . $categoryName)), '', $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$key = array_keys(EconomyShopForm::getShop()[$this->category])[$selectedOption];
		if(is_string(key(EconomyShopForm::getShop()[$this->category][$key]))){
			$player->sendForm(new EconomyShopSubCategoryForm($player, 'forms.economy.shop.category.' . $this->category . '.' . $key, EconomyShopForm::getShop()[$this->category][$key]));
		}else{
			$option = EconomyShopForm::getShop()[$this->category][$key];
			$player->sendForm(new EconomyShopItemsForm(
			    $player->translate('forms.economy.shop.category.' . $this->category . '.' . $key),
                $option,
                [self::class, $player, $this->category]
            ));
		}
	}

	public function onClose(Player $player) : void{
        /** @var SGPlayer $player */
        $player->sendForm(new EconomyShopForm($player));
    }
}