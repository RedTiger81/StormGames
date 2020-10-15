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
use pocketmine\form\element\Label;
use pocketmine\form\element\Slider;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class BuyCrateKeyForm extends CustomForm{

	/** @var string */
	protected $tier;
	/** @var int */
	protected $price;

	/** @var string */
	protected $priceTranslated = 'coins';

	public function __construct(SGPlayer $player, string $tier, int $price){
		$this->tier = $tier;
		$this->price = $price;
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate("forms.buyCrateKey.title")), [
			new Label('text', TextFormat::AQUA . $player->translate("forms.buyCrateKey.text", [TextFormat::WHITE . $this->tier . TextFormat::AQUA, TextFormat::WHITE . $this->price . " " . $player->translate($this->priceTranslated) . TextFormat::AQUA])),
			new Slider('amount', $player->translate('default.amount'), 1, 50)
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$amount = (int) $data->getFloat('amount');
		$price = $this->price * $amount;

		if($player->reduceCoins($price)){
			$player->addCrateKeys($this->tier, $amount);
			$player->sendMessage(Prefix::MAIN . TextFormat::GREEN . $player->translate('forms.buyCrateKey.bought', [TextFormat::WHITE . $amount . TextFormat::GREEN, TextFormat::WHITE . $price . TextFormat::GREEN]));
		}else{
			$player->sendMessage(Prefix::MAIN . TextFormat::RED . $player->translate('error.generic.noCoins'));
		}
	}
}