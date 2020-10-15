<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\economy;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Label;
use pocketmine\form\element\Slider;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;

class EconomySellItemForm extends CustomForm{

	/** @var int */
	private $id;

	public function __construct(SGPlayer $player, int $id){
		$count = 0;
		foreach($player->getInventory()->getContents() as $item){
			if($item->getId() === $id and ($item->getMeta() === 0 or !($item instanceof Durable))){
				$count += $item->getCount();
			}
		}
		$this->id = $id;
		parent::__construct($player->translate('forms.economy.sell'), [
			new Label('label', TextFormat::AQUA . $player->translate('forms.economy.sell.item', [
				TextFormat::WHITE . $id . TextFormat::AQUA,
				TextFormat::WHITE . EconomySellForm::ITEMS[$id],
			])),
			new Slider('amount', $player->translate('default.amount'), 1, $count)
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new EconomySellModalForm($player, $this->id, (int) $data->getFloat('amount')));
	}

}