<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\PromotionManager;
use StormGames\SGCore\SGPlayer;

class PromotionForm extends CustomForm{

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GOLD . $player->translate('forms.menu.promotion') ), [
			new Input('code', $player->translate("forms.menu.promotion.code"))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		static $errorMessages = [
			PromotionManager::ERROR_CODE_NOT_FOUND => "forms.menu.promotion.not.found",
			PromotionManager::ERROR_USED_ALL_CODES => "forms.menu.promotion.used.all.codes",
			PromotionManager::ERROR_USED => "forms.menu.promotion.used"
		];
		$promotionCode = $data->getString('code');
		$error = PromotionManager::useCode($promotionCode, $player);
		$player->sendMessage(Prefix::MAIN . $player->translate($errorMessages[$error] ?? 'forms.menu.promotion.success', [TextFormat::GOLD . $promotionCode, $error]));
	}
}