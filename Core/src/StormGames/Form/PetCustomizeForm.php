<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Pet\Pet;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class PetCustomizeForm extends CustomForm{

	/** @var Pet */
	private $pet;

	public function __construct(SGPlayer $player, Pet $pet){
		$this->pet = $pet;
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('forms.pet')), [
			new Input('petName', $player->translate('forms.pet.name'), '', $pet->getNameTag())
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$newName = $data->getString('petName');

		if($newName === ''){
			$this->pet->setNameTag($newName);
		}else{
			if(strlen($newName) <= 30 and (preg_match('/^[a-z0-9 ]+$/i', $newName) > 0)){
				$this->pet->setNameTag(TextFormat::YELLOW . $newName);
				$player->sendMessage(Prefix::PETS() . TextFormat::GREEN . $player->translate('forms.pet.update'));
			}else{
				$player->sendMessage(Prefix::PETS() . TextFormat::RED . $player->translate('forms.pet.name.error', [30]));
			}
		}
	}

}