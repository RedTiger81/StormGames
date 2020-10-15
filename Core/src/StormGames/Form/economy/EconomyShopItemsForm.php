<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\economy;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;
use StormGames\SGCore\utils\IconUtils;

class EconomyShopItemsForm extends MenuForm{

	/** @var array */
	private $items;
	/** @var array */
    private $classInfo;

    public function __construct(string $name, array $items, array $classInfo, bool $titlePrefix = true){
		$this->items = $items;
		$this->classInfo = $classInfo;
		$options = [];
		foreach($this->items as $itemData){
			/** @var Item $item */
			$item = $itemData['item'];
			$options[] = new MenuOption(TextFormat::DARK_GREEN . $item->getVanillaName() . TextFormat::GRAY . ' - ' . TextFormat::DARK_GREEN . Utils::addMonetaryUnit($itemData['price']), IconUtils::getFormIcon($item));
		}
		parent::__construct($titlePrefix ? sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $name) : $name, '', $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new EconomyShopBuyForm(
		    $player,
            $this->items[array_keys($this->items)[$selectedOption]],
            [$this->title, $this->items, $this->classInfo, false]
        ));
	}

	public function onClose(Player $player) : void{
	    $class = array_shift($this->classInfo);
	    $player->sendForm(new $class(...$this->classInfo));
    }
}