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
use StormGames\SGCore\SGPlayer;
use StormGames\Form\mission\MissionInfoForm;
use StormGames\SGCore\mission\Mission;
use StormGames\Prefix;

class MissionsForm extends MenuForm{

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('mission')), '', array_map(function(string $mission) use($player){
			/** @noinspection PhpUndefinedMethodInspection */
			return new MenuOption(($player->isMissionCompleted($mission::getId()) ? TextFormat::DARK_GREEN : TextFormat::DARK_RED) . $player->translate($mission::getTranslateKey()), $mission::getFormIcon());
		}, Mission::getAllMissions()));
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new MissionInfoForm($player, $selectedOption));
	}
}