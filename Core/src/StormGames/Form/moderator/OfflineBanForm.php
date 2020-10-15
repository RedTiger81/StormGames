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
use StormGames\Form\ModeratorToolsForm;
use StormGames\Prefix;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class OfflineBanForm extends CustomForm{

	public function __construct(SGPlayer $player, string $title){
		parent::__construct($title, [
			new Input('player', $player->translate('forms.moderatortools.player')),
			new Dropdown('reason', $player->translate('forms.moderatortools.reason'), ['hack', 'harassment', 'insult', 'illegal-behavior', 'spam', 'advertise', 'swear', 'other']),
			new Input('time', $player->translate('forms.moderatortools.time'), '+1 month')
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$time = strtotime($data->getString('time'));
		if($time === false){
			$player->sendForm(new OfflineBanForm($player, $this->title));
		}else{
			/** @noinspection PhpUndefinedMethodInspection */
			$reason = $this->getElement(1)->getOption($data->getInt('reason'));
			$name = strtolower($data->getString('player'));

			$db = SGCore::getDatabase();
			if(($db->select(SGCore::TABLE_CRIMINAL_RECORDS, '*', $extra = 'WHERE username=\''. $name .'\'')->num_rows ?? 0) !== 0){
				$db->query(sprintf('UPDATE %s SET banInfo=\'%s\', banCount=banCount+1 ' . $extra, SGCore::TABLE_CRIMINAL_RECORDS, implode(':', [1, $reason, $time, $player->getName()])));
				if(($bannedPlayer = $player->getServer()->getPlayerExact($name)) !== null){
					$bannedPlayer->disconnect('', 'You are banned!');
				}

				/** @var SGPlayer $onlinePlayer */
				foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer){
					$onlinePlayer->sendMessage(Prefix::MOD() . $onlinePlayer->translate('forms.moderatortools.ban.banned', [$name, $player->getName()]));
				}
			}else{
				$player->sendMessage(Prefix::MOD() . $player->translate('error.generic.playerNotFound', [$name]));
			}
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new ModeratorToolsForm($player));
	}
}