<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu\cosmetic;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Pet\Pet;
use StormGames\Pet\PetManager;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class CosmeticPetsForm extends MenuForm{

	/** @var MenuOption[] */
	private $buttons;

	public function __construct(SGPlayer $player){
		$this->buttons = Pet::canUse($player) ? PetManager::getMenuOptions($player) : [];
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GOLD . $player->translate('forms.cosmetics.pet')), $player->isVip() ? "" : TextFormat::GRAY . $player->translate("error.only.member", [TextFormat::GOLD . TextFormat::BOLD . "VIP" . TextFormat::RESET . TextFormat::GRAY]), $this->buttons);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		/** @var string|Pet $petClass */
		$petClass = array_keys($this->buttons)[$selectedOption];
		if($petClass === 'remove'){
			$player->getCosmetics()->setPet(null);
			$player->sendMessage(TextFormat::RED . $player->translate("forms.cosmetics.pet.removed"));
		}else{
			if($petClass::canUse($player)){
				$player->getCosmetics()->setPet(PetManager::givePet($player, $petClass));
				$player->sendMessage(TextFormat::GREEN . $player->translate("forms.cosmetics.pet.spawned", [TextFormat::YELLOW . $player->translate($petClass::getPetName()) . TextFormat::GREEN]));
			}else{
				$player->sendMessage(TextFormat::RED . $player->translate("forms.cosmetics.pet.permission"));
			}
		}
	}
}