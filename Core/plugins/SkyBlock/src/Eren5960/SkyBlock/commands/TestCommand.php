<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\commands;

use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\island\Member;
use Eren5960\SkyBlock\pass\PassManager;
use Frago9876543210\Specter\Specter;
use Frago9876543210\Specter\SpecterInfo;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use StormGames\SGCore\blocks\HopperBlock;

class TestCommand extends Command{
	public function __construct(){
		parent::__construct("test", "Test");
		$this->setPermission(DefaultPermissions::ROOT);
	}

	/**
	 * @param Player|CommandSender $sender
	 * @param string               $commandLabel
	 * @param array                $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)) return false;

		$sender->getInventory()->addItem(ItemFactory::get(ItemIds::HOPPER));
		$sender->getWorld()->setBlock($sender->getPosition()->add(1, 0, 2), new HopperBlock());

		return true;
	}
}