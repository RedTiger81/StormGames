<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\menu\cosmetic\CosmeticCapesForm;
use StormGames\Form\menu\cosmetic\CosmeticParticleCategoriesForm;
use StormGames\Form\menu\cosmetic\CosmeticPetsForm;
use StormGames\Prefix;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class CosmeticForm extends MenuForm{

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.cosmetics')), "", [
			new MenuOption($player->translate("forms.cosmetics.particle"), new FormIcon("textures/items/magma_cream", FormIcon::IMAGE_TYPE_PATH)),
			new MenuOption($player->translate("forms.cosmetics.cape"), new FormIcon("http://minestormpe.com/image/icon/cape.png")),
			new MenuOption($player->translate("forms.cosmetics.pet") . TextFormat::GOLD . TextFormat::BOLD . " VIP", new FormIcon("textures/items/lead", FormIcon::IMAGE_TYPE_PATH))
		]);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		static $class = [
			CosmeticParticleCategoriesForm::class,
			CosmeticCapesForm::class,
			CosmeticPetsForm::class
		];

		$player->sendForm(new $class[$selectedOption]($player));
	}

	public function onClose(Player $player) : void{
		$class = SGCore::$formClasses["menu"];
		$player->sendForm(new $class($player));
	}
}