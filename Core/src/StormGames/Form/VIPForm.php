<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\entity\Attribute;
use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Toggle;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;

class VIPForm extends CustomForm{

	public function __construct(SGPlayer $player){
		$moveSpeed = $player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GOLD . $player->translate('forms.vip')), [
			new Toggle('heal', $player->translate('forms.vip.heal'), $player->getHealth() === $player->getMaxHealth()),
			new Toggle('feed', $player->translate('forms.vip.feed'), $player->getAbsorption() === 20),
			new Toggle('runFast', $player->translate('forms.vip.runFast'), $moveSpeed->getValue() === 0.3)
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		if($data->getBool('heal')){
			$player->setHealth($player->getMaxHealth());
		}
		if($data->getBool('feed')){
			$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		}

		$player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED)->setValue($data->getBool('runFast') ? 0.3 : 0.1);
	}
}