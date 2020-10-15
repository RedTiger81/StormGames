<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\moderator;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class TranslateForm extends CustomForm{
	public function __construct(SGPlayer $player, string $title){
		parent::__construct($title, [
			new Dropdown('sourceLang', $player->translate('forms.moderatortools.translate.sourceLang'), ['tr', 'en', 'de']),
			new Dropdown('targetLang', $player->translate('forms.moderatortools.translate.targetLang'), ['tr', 'en', 'de']),
			new Input('text', $player->translate('forms.moderatortools.translate.text'))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @noinspection PhpUndefinedMethodInspection */
		$player->sendMessage(Prefix::TRANSLATE() . Utils::translateText($this->getElement(0)->getOption($data->getInt('sourceLang')), $this->getElement(1)->getOption($data->getInt('targetLang')), $data->getString('text')));
	}
}