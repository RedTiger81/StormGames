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
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\anvil\AnvilChangeNameForm;
use StormGames\Form\anvil\AnvilEnchantForm;
use StormGames\Form\anvil\AnvilRepairForm;
use StormGames\Prefix;
use StormGames\SGCore\utils\IconUtils;

class AnvilForm extends MenuForm{

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.anvil')), '', [
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.anvil.changeName'), IconUtils::get('anvil/changeName')),
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.anvil.enchant'), IconUtils::get('anvil/ench')),
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.anvil.repair'), IconUtils::get('anvil/repair')),
		]);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$class = [
			AnvilChangeNameForm::class,
			AnvilEnchantForm::class,
			AnvilRepairForm::class
		];
		$class = $class[$selectedOption];

		if(!($player->getInventory()->getItemInHand() instanceof Durable)){
			$player->sendMessage(Prefix::ANVIL() . TextFormat::RED . $player->translate('forms.anvil.changeName.fail'));
		}else{
			$player->sendForm(new $class($player));
		}
	}
}