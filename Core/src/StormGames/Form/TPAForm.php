<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\ModalForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;

class TPAForm extends ModalForm{

	/** @var array */
	private static $denied;

	public function __construct(SGPlayer $player){
		parent::__construct(
		    sprintf(Prefix::FORM_TITLE, TextFormat::GOLD . $player->translate('forms.tpa')),
            TextFormat::WHITE . $player->translate('forms.tpa.text'),
            TextFormat::DARK_GREEN . $player->translate('forms.on'),
            TextFormat::DARK_RED. $player->translate('forms.off')
        );
	}

	public function onSubmit(Player $player, bool $choice) : void{
		/** @var SGPlayer $player */
		$text = $choice ? 'on' : 'off';
		self::{$text}($player);
		$player->sendMessage(Prefix::TPA() . $player->translate('forms.tpa.' . $text));
	}

	public static function isDenied(Player $player) : bool{
		return isset(self::$denied[$player->getId()]);
	}

	public static function off(Player $player) : void{
		self::$denied[$player->getId()] = true;
	}

	public static function on(Player $player) : void{
		unset(self::$denied[$player->getId()]);
	}
}