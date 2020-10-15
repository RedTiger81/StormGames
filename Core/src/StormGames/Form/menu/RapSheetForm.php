<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu;

use pocketmine\form\MenuForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class RapSheetForm extends MenuForm{

	/** @var bool */
	private $exit;

	public function __construct(SGPlayer $player, SGPlayer $target = null, bool $exit = false){
		$this->exit = $exit;
		if($target === null){
			$target = $player;
		}
		$criminalRecord = $target->getCriminalRecord();

		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::RED . $player->translate('forms.menu.rapsheet')), $player->translate("forms.menu.rapsheet.text", [
			TextFormat::WHITE . $target->getName(),
			TextFormat::WHITE . $criminalRecord->getBanReason(),
			TextFormat::WHITE . $criminalRecord->getBannedBy(),
			TextFormat::WHITE . $criminalRecord->getBanCount(),
			TextFormat::WHITE . $criminalRecord->getKickCount()
		]), []);
	}

	public function onClose(Player $player) : void{
		if(!$this->exit){
			$class = SGCore::$formClasses["menu"];
			$player->sendForm(new $class($player));
		}
	}
}