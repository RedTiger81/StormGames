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
use pocketmine\utils\TextFormat;
use StormGames\Form\ModeratorToolsForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class KickForm extends CustomForm{

	public function __construct(SGPlayer $player, string $title){
		parent::__construct($title, [
			new Dropdown('player', $player->translate('forms.moderatortools.player'), array_map(function(SGPlayer $player) : string{ return $player->getName(); }, array_values($player->getServer()->getOnlinePlayers()))),
			new Input('reason', $player->translate('forms.moderatortools.reason'))
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		/** @noinspection PhpUndefinedMethodInspection */
		$name = $this->getElement(0)->getOption($data->getInt('player'));
		$target = $player->getServer()->getPlayerExact($name);
		/** @var SGPlayer $target */
		if($target !== null){
			$target->getCriminalRecord()->kick($data->getString('reason'));

			/** @var SGPlayer $onlinePlayer */
			foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer){
				$onlinePlayer->sendMessage(Prefix::MOD() . TextFormat::GREEN . $onlinePlayer->translate('forms.moderatortools.kick.kicked', [$name, $player->getName()]));
			}
		}else{
			$player->sendMessage(Prefix::MOD() . TextFormat::RED . $player->translate('forms.moderatortools.kick.quited', [$name]));
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new ModeratorToolsForm($player));
	}
}