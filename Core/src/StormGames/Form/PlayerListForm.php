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
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class PlayerListForm extends MenuForm{

	public function __construct(SGPlayer $player){
		/** @var SGPlayer[] $players */
		$players = $player->getServer()->getOnlinePlayers();
		usort($players, function(SGPlayer $a, SGPlayer $b) : int{
			return ($b->getGroup() === null ? 0 : $b->getGroup()->getPriority()) <=> ($a->getGroup() === null ? 0 : $a->getGroup()->getPriority());
		});
		parent::__construct(
			sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate("forms.player.list.title") . " " . TextFormat::DARK_GREEN . count($players) . TextFormat::DARK_GRAY . "/" . TextFormat::DARK_GREEN . $player->getServer()->getMaxPlayers()),
			TextFormat::RED . "█ " . TextFormat::GRAY . $player->translate("forms.player.list.owner") . TextFormat::EOL .
			TextFormat::BLUE . "█ " . TextFormat::GRAY . $player->translate("forms.player.list.mod") . TextFormat::EOL .
			TextFormat::GOLD . "█ " . TextFormat::GRAY . $player->translate("forms.player.list.premium"),
			array_map(function(SGPlayer $player) : MenuOption{
				$color = $player->isOwner() ? TextFormat::RED : ($player->isOwner() ? TextFormat::BLUE : ($player->isVip() ? TextFormat::GOLD : TextFormat::DARK_GRAY));
				return new MenuOption($color . $player->getName(), new FormIcon(Utils::getSkinHeadImageURL($player)));
			}, $players)
		);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$name = TextFormat::clean($this->getOption($selectedOption)->getText());
		$target = $player->getServer()->getPlayerExact($name);

		if($target !== null){
			$class = PlayerMenuForm::$class[0];
			$player->sendForm(new $class($player, $target, true));
		}else{
			$player->sendMessage(Prefix::MAIN . TextFormat::RED . $player->translate("error.generic.playerIsOffline"));
		}
	}
}