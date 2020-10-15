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
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\EconomyForm;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;
use StormGames\SGCore\utils\IconUtils;

class EconomySellForm extends MenuForm{

	public const ITEMS = [
	    ItemIds::STONE => 2,
	    ItemIds::COBBLESTONE => 1,
	    ItemIds::MELON_SLICE => 10,
	    ItemIds::MELON_BLOCK => 20,
	    ItemIds::PUMPKIN => 20,
	    ItemIds::WHEAT => 20,
	    ItemIds::CARROT => 22,
		ItemIds::CACTUS => 25,
		ItemIds::SUGARCANE => 30,
	    ItemIds::POTATO => 25,
	    ItemIds::BEETROOT => 40,
	    ItemIds::DIAMOND => 200,
	    ItemIds::EMERALD => 175,
	    ItemIds::GOLD_INGOT => 150,
	    ItemIds::IRON_INGOT => 125,
	    ItemIds::COAL => 25,
	    ItemIds::REDSTONE => 10,
	    ItemIds::DYE => 10,
        ItemIds::LOG => 3,
        ItemIds::LOG2 => 3,
		ItemIds::OBSIDIAN => 100,
		ItemIds::ROTTEN_FLESH => 30,
		ItemIds::BEEF => 30
    ];
	/** @var int[] */
	private $options = [];

	public function __construct(SGPlayer $player){
		foreach($player->getInventory()->getContents() as $item){
			if(isset(self::ITEMS[$item->getId()]) and !isset($options[$item->getId()])){
				$this->options[$item->getId()] = new MenuOption(TextFormat::BOLD . $item->getName() . TextFormat::RESET . TextFormat::EOL . Utils::addMonetaryUnit(self::ITEMS[$item->getId()]), IconUtils::getFormIcon($item));
			}
		}
		if(!empty($this->options)){
			$this->options[-1] = new MenuOption($player->translate('forms.economy.sell.all'));
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.economy.sell')), '', $this->options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$this->options = array_keys($this->options);
		if($this->options[$selectedOption] === -1){
			$player->sendForm(new EconomySellModalForm($player, -1));
		}else{
			$player->sendForm(new EconomySellItemForm($player, $this->options[$selectedOption]));
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new EconomyForm($player));
	}
}