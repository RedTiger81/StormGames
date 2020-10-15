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
use StormGames\Prefix;
use StormGames\SGCore\entity\Crate;
use StormGames\SGCore\SGPlayer;

class CrateForm extends MenuForm{

	/** @var Crate */
	protected $crate;

	public function __construct(SGPlayer $player, Crate $crate){
		$this->crate = $crate;
		$tier = $crate->getCrateTier();
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.crate.title')), $player->translate("forms.crate.text", [$player->getCrateKeys($tier) . " " . $player->translate("crate.tier.$tier")]), [
			new MenuOption($player->translate("forms.crate.use.key")),
			new MenuOption($player->translate("forms.crate.buy.key"))
		]);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		if($selectedOption === 0){
			$tier = $this->crate->getCrateTier();
			$translatedTier = $player->translate("crate.tier.$tier");
			if($player->getCrateKeys($tier) > 0){
				if($this->crate->openCrate($player)){
					$player->subtractCrateKeys($tier);
					$player->sendMessage(Prefix::MAIN . TextFormat::GREEN . $player->translate("forms.crate.opening", [TextFormat::WHITE . $translatedTier . TextFormat::GREEN]));
				}else{
					$player->sendMessage(Prefix::MAIN . TextFormat::RED . $player->translate("forms.crate.isnt.available", [$translatedTier]));
				}
			}else{
				$player->sendMessage(Prefix::MAIN . TextFormat::RED . $player->translate("forms.crate.dont.have.keys", [$translatedTier]));
			}
		}else{
			$player->sendForm($this->crate->getContent()->getBuyForm($player));
		}
	}
}