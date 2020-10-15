<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu\cosmetic;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Label;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\menu\CosmeticForm;
use StormGames\Prefix;
use StormGames\SGCore\entity\utils\Skins;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;

class CosmeticCapesForm extends CustomForm{

	/** @var array */
	private $options;

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GOLD . $player->translate('forms.cosmetics.cape')), $this->getElements($player));
	}

	private function getElements(SGPlayer $player) : array{
		$this->options = [];
		foreach(Skins::getCapes() as $capeName => $cape){
			if($player->hasPermission(DefaultPermissions::ROOT_CAPE . $capeName)){
				$this->options[$capeName] = $player->translate("capes.$capeName");
			}
		}
		$this->options[""] = $player->translate("capes.none");

		return [
			new Label('label', $player->translate("forms.cosmetics.cape.text")),
			new Dropdown('cape', TextFormat::BLUE . $player->translate("forms.cosmetics.cape"), array_values($this->options), array_search($player->getCosmetics()->getCape(), $this->options = array_keys($this->options)))
		];
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$player->getCosmetics()->setCape($this->options[$data->getInt('cape')]);
		$player->sendMessage(Prefix::COSMETIC() . TextFormat::GREEN . $player->translate("forms.cosmetics.cape.changed"));
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new CosmeticForm($player));
	}

}