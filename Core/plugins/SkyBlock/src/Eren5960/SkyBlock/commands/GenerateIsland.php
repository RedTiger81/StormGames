<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\commands;

use Eren5960\SkyBlock\generators\EndIslandGenerator;
use Eren5960\SkyBlock\generators\NetherIslandGenerator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;

class GenerateIsland extends Command{
    public function __construct(){
        parent::__construct("gi", "Generate IslandBase", null, []);
        $this->setPermission(DefaultPermissions::ROOT);
    }

    /**
     * @param Player|CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)) return false;
        $manager = Server::getInstance()->getWorldManager();
        if(isset($args[0])){
        	$str = $args[0] . "island";
        	Server::getInstance()->getWorldManager()->generateWorld($str, time(), GeneratorManager::getGenerator($str));
        	$manager->loadWorld($str);
	        $world = $manager->getWorldByName($str);

	        $sender->sendMessage("§8» §aHerşey hazır");
        }
        return true;
    }
}