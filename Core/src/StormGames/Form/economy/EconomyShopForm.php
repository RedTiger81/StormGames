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
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\EconomyForm;
use StormGames\Prefix;

class EconomyShopForm extends MenuForm{
    /** @var array */
    private static $shop = [];

    public function __construct(SGPlayer $player){
        $options = [];
        foreach(self::getShop() as $categoryName => $value){
            $options[] = new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.economy.shop.category.' . $categoryName), isset($value['icon_url']) ? new FormIcon($value['icon_url']) : null);
        }
        parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.economy.shop')), '', $options);
    }

    public static function getShop() : array{
        if(empty(self::$shop)){
            $json = json_decode(file_get_contents(__DIR__ . '/utils/shop.json'), true);
            foreach($json as $topCategoryName => $subCategories){
                foreach($subCategories as $subCategoryName => $includes){
                    foreach($includes as $key => $value){
                        if(is_string($key)){ // category -> category
                            foreach($value as $v){
                                if(!($item = self::itemDataToItem($v))->isNull()){
                                    self::$shop[$topCategoryName][$subCategoryName][$key][] = ["item" => $item, "price" => $v["price"]];
                                }
                            }
                        }else{
                            if(!($item = self::itemDataToItem($value))->isNull()){
                                self::$shop[$topCategoryName][$subCategoryName][] = ["item" => $item, "price" => $value["price"]];
                            }
                        }
                    }
                }
            }
        }

        return self::$shop;
    }

    private static function itemDataToItem(array $itemData) : Item{
        return ItemFactory::get(
            $itemData["id"],
            $itemData["meta"] ?? 0,
            $itemData["count"] ?? 1
            //$itemData["nbt"] ?? null // FIXME
        );
    }

    public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$option = array_keys(self::getShop())[$selectedOption];
		$player->sendForm(new EconomyShopCategoryForm($player, $option));
	}

	public function onClose(Player $player) : void{
    	/** @var SGPlayer $player */
		$player->sendForm(new EconomyForm($player));
	}
}