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
use StormGames\SGCore\Top;
use StormGames\Prefix;
use StormGames\SGCore\utils\IconUtils;

class TopsForm extends MenuForm{
	private const FORMAT = TextFormat::BOLD . TextFormat::GOLD . '%d. ' . TextFormat::RESET . '%s ' . TextFormat::YELLOW . '%s' . TextFormat::EOL;

	private const OPTIONS = [
		['kills'], ['deaths'], ['money', 'economy/richest']
	];

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::LIGHT_PURPLE . $player->translate('forms.tops')), '', array_map(function(array $data) use($player): MenuOption{
			return new MenuOption(TextFormat::DARK_PURPLE . $player->translate('forms.tops.' . $data[0]), isset($data[1]) ? IconUtils::get($data[1]) : null);
		}, self::OPTIONS));
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		$selected = self::OPTIONS[$selectedOption][0];
		$call = call_user_func(Top::class . '::' . $selected, 30);
		$text = '';

		foreach($call as $key => $value){
			$text .= sprintf(self::FORMAT, $key + 1, $value[0], $value[1]);
		}

		$player->sendForm(new class($this->getOption($selectedOption)->getText(), $text, []) extends MenuForm{
			public function onClose(Player $player) : void{
				/** @var SGPlayer $player */
				$player->sendForm(new TopsForm($player));
			}
		});
	}
}