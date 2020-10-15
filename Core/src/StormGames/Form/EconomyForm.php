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
use StormGames\Prefix;
use StormGames\SGCore\utils\IconUtils;
use StormGames\SGCore\utils\Utils;

class EconomyForm extends MenuForm{
	private const OPTIONS = [
		['shop', '\StormGames\Form\economy\EconomyShopForm'],
		['coinShop', '\StormGames\Form\economy\EconomyCoinShopForm'],
		['sell', '\StormGames\Form\economy\EconomySellForm'],
		['otoSell', '\StormGames\Form\economy\EconomyOtomaticSellForm'],
		['sendMoney', '\StormGames\Form\economy\EconomySendMoneyForm']
	];

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.economy')), '§2Paran: §f' . Utils::addMonetaryUnit($player->getMoney()), array_map(function(array $data) use($player): MenuOption{
			return new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.economy.' . $data[0]), IconUtils::get('economy/' . $data[0]));
		}, self::OPTIONS));
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		list($name, $class) = self::OPTIONS[$selectedOption];
		if(in_array($name, ['coinShop', 'otoSell'])){
			new $class($player);
			return;
		}
		$player->sendForm(new $class($player));
	}
}