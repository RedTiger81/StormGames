<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\menu\CosmeticForm;
use StormGames\Form\menu\NavigatorForm;
use StormGames\Form\menu\ProfileForm;
use StormGames\Form\menu\PromotionForm;
use StormGames\Form\menu\RapSheetForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class PlayerMenuForm extends MenuForm{

	public static $class = [
		ProfileForm::class,
		NavigatorForm::class,
		CosmeticForm::class,
		RapSheetForm::class,
		PromotionForm::class
	];

	public function __construct(SGPlayer $player){
		parent::__construct(
			sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.menu.title')),
			TextFormat::GREEN . $player->translate("forms.menu.text"),
			$this->getButtons($player)
		);
	}

	protected function getButtons(SGPlayer $player) : array{
		return [
			new MenuOption($player->translate("forms.menu.profile"), new FormIcon(Utils::getSkinHeadImageURL($player))),
			new MenuOption($player->translate("forms.menu.navigator"), new FormIcon("textures/items/compass_item", FormIcon::IMAGE_TYPE_PATH)),
			new MenuOption($player->translate("forms.menu.cosmetics"), new FormIcon("textures/items/magma_cream", FormIcon::IMAGE_TYPE_PATH)),
			new MenuOption($player->translate("forms.menu.rapsheet"), new FormIcon("http://minestormpe.com/image/icon/kelepce.png")),
			new MenuOption($player->translate("forms.menu.promotion"), new FormIcon("textures/items/gold_ingot", FormIcon::IMAGE_TYPE_PATH))
		];
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		$player->sendForm(new self::$class[$selectedOption]($player));
	}

}