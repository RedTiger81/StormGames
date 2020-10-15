<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\moderator;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\ModeratorToolsForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class ClearInventoryForm extends MenuForm{
	public function __construct(SGPlayer $player, string $title){
		parent::__construct($title, '', array_map(function(SGPlayer $player) : MenuOption{
			return new MenuOption($player->getName(), new FormIcon(Utils::getSkinHeadImageURL($player)));
		}, $player->getServer()->getOnlinePlayers()));
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$name = $this->getOption($selectedOption)->getText();
		$target = $player->getServer()->getPlayerExact($name);

		if($target !== null){
			$target->getInventory()->clearAll();
			$target->getArmorInventory()->clearAll();
		}else{
			$player->sendMessage(Prefix::MAIN . TextFormat::RED . $player->translate('error.generic.playerIsOffline'));
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new ModeratorToolsForm($player));
	}
}