<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class ModeratorToolsForm extends MenuForm{
	public const OPTIONS = [
		['ban', '\StormGames\Form\moderator\BanForm', 'hammer_l'],
		['offlineBan', '\StormGames\Form\moderator\OfflineBanForm', 'hammer_l_disabled'],
		['unban', '\StormGames\Form\moderator\UnbanForm', 'unLock'],
		['kick', '\StormGames\Form\moderator\KickForm', 'empty_armor_slot_boots'],
		['translate', '\StormGames\Form\moderator\TranslateForm', 'comment'],
		['clearInventory', '\StormGames\Form\moderator\ClearInventoryForm', 'selected_hotbar_slot'],
	];

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.moderatortools.title')), '', array_map(function(array $data) use($player) : MenuOption{
			return new MenuOption($player->translate('forms.moderatortools.' . $data[0]), new FormIcon('textures/ui/' . $data[2], FormIcon::IMAGE_TYPE_PATH));
		}, self::OPTIONS));
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		list(, $class) = self::OPTIONS[$selectedOption];
		$player->sendForm(new $class($player, sprintf(Prefix::FORM_TITLE, $this->getOption($selectedOption)->getText())));
	}
}