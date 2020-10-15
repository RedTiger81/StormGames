<?php

declare(strict_types=1);

namespace Eren5960;

use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;

class SlotCommand extends Command{

	/**
	 * @param CommandSender|SkyPlayer $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof SkyPlayer){
			Slot::prepareBetChest($sender);
		}else{
			$sender->sendMessage("Oyun içinde kullan aq salağı");
		}
		return true;
	}
}