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

class BanForm extends CustomForm{

	public function __construct(SGPlayer $player, string $title){
		parent::__construct($title, [
			new Dropdown('player', $player->translate('forms.moderatortools.player'), array_map(function(SGPlayer $player) : string{ return $player->getName(); }, array_values($player->getServer()->getOnlinePlayers()))),
			new Dropdown('reason', $player->translate('forms.moderatortools.reason'), ['hack', 'harassment', 'insult', 'illegal-behavior', 'spam', 'advertise', 'swear', 'other']),
			new Input('time', $player->translate('forms.moderatortools.time'), '+1 month')
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		/** @noinspection PhpUndefinedMethodInspection */
		$name = $this->getElement(0)->getOption($data->getInt('player'));

		$target = $player->getServer()->getPlayerExact($name);
		if($target instanceof SGPlayer){
			$criminalRecord = $target->getCriminalRecord();
			$timeStamp = $data->getString('time');
			$timeStamp = strtotime($timeStamp);
			if($timeStamp === false){
				$player->sendForm(new BanForm($player, $this->title));
			}else{
				/** @noinspection PhpUndefinedMethodInspection */
				$criminalRecord->setBanned(true, $this->getElement(1)->getOption($data->getInt('reason')), $timeStamp, $player->getLowerCaseName());
				/** @var SGPlayer $onlinePlayer */
				foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer){
					$onlinePlayer->sendMessage(Prefix::MOD() . $onlinePlayer->translate('forms.moderatortools.ban.banned', [$name, $player->getName()]));
				}
			}
		}else{
			$player->sendMessage(Prefix::MOD() . TextFormat::RED . $player->translate('forms.moderatortools.ban.quited', [$name]));
		}

	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new ModeratorToolsForm($player));
	}
}