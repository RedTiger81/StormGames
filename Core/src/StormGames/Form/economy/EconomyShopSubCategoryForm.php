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

class EconomyShopSubCategoryForm extends MenuForm{

    /** @var array */
    private $data = [];
    /** @var string */
    private $translateName;

    public function __construct(SGPlayer $player, string $translateName, array $data){
        $this->data = $data;
        $this->translateName = $translateName;
        $options = [];
        foreach($this->data as $key => $value){
            $options[] = new MenuOption(TextFormat::DARK_GREEN . $player->translate($translateName . '.' . $key), isset($value['icon_url']) ? new FormIcon($value['icon_url']) : null);
        }
        parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate($translateName)), '', $options);
    }

    public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$key = array_keys($this->data)[$selectedOption];
		$player->sendForm(new EconomyShopItemsForm(
		    $player->translate($this->translateName . '.' . $key),
            $this->data[$key],
            [self::class, $player, $this->translateName, $this->data]
        ));
	}

	public function onClose(Player $player) : void{
        /** @var SGPlayer $player */
        preg_match("/category\.(\w+)/", $this->translateName, $matches);
        $player->sendForm(new EconomyShopCategoryForm($player, $matches[1]));
    }
}