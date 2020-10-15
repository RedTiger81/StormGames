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

class NavigatorForm extends MenuForm{
	public const SERVER_IPS = [
		["sw.stormgames.net", 19132]
	];

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::AQUA . $player->translate('forms.menu.navigator')), "§cSOON", []);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		$player->transfer(...self::SERVER_IPS[$selectedOption]);
	}

	public function onClose(Player $player) : void{
		$class = SGCore::$formClasses["menu"];
		$player->sendForm(new $class($player));
	}
}