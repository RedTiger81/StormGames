<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\anvil;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Label;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;
use StormGames\SGCore\permission\DefaultPermissions;

class AnvilChangeNameForm extends CustomForm{

	public const CHANGE_NAME_COST = 1000;

	public function __construct(SGPlayer $player){
		parent::__construct(TextFormat::YELLOW . $player->translate('forms.anvil.changeName'), [
			new Label('label', TextFormat::GRAY . $player->translate('forms.anvil.changeName.text', [
					sprintf(Utils::addMonetaryUnit(self::CHANGE_NAME_COST, true), TextFormat::GOLD, TextFormat::YELLOW) . TextFormat::GRAY
				])),
			new Input('newName', $player->translate('forms.anvil.changeName.newName'))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$newName = $data->getString('newName');
		if(!empty($newName)){
			if($player->reduceMoney(self::CHANGE_NAME_COST)){
				if(!$player->hasPermission(DefaultPermissions::VIP_PLUS)){
					$newName = TextFormat::clean($newName);
				}
				$newName = TextFormat::RESET . TextFormat::WHITE . $newName;

				$player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCustomName($newName));
				$player->sendMessage(Prefix::ANVIL() . $player->translate('forms.anvil.changeName.success'));
			}else{
				$player->sendMessage(Prefix::ANVIL() . TextFormat::RED . $player->translate('error.generic.noMoney'));
			}
		}else{
			$player->sendMessage(Prefix::ANVIL() . TextFormat::RED . $player->translate('forms.anvil.changeName.notBlank'));
		}
	}
}