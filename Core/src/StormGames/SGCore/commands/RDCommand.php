<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\SGPlayer;

abstract class RDCommand extends Command{

	public function getDescriptionFor(CommandSender $sender) : string{
		return Language::translate($sender instanceof SGPlayer ? $sender->getLanguage() : Language::DEFAULT_LANGUAGE, 'commands.' . $this->getDescription() . '.desc');
	}
}