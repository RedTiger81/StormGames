<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\commands;

use Eren5960\SkyBlock\SkyBlock;
use Eren5960\SkyBlock\utils\Compression;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class RegisterIslandCommand extends Command{
    public function __construct(){
        parent::__construct("ri", "Register Islands", null, []);
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

        $name = array_shift($args);
        $level_cost = (int) array_shift($args);
        $money_cost = (int) array_shift($args);
	    $xp_cost = array_shift($args);
        if($xp_cost === null){
            $sender->sendMessage("/ri isim levelcost moneycost xpcost");
            return false;
        }
	    $xp_cost = (int)  $xp_cost;

        $level_name = $sender->getWorld()->getFolderName();
        $sender->getWorld()->save();
        Server::getInstance()->getWorldManager()->unloadWorld($sender->getWorld());
        sleep(3);
        $world_path = Server::getInstance()->getDataPath() . 'worlds' . DS;
        (new Config($world_path . $level_name . DS . 'config.yml', 2, ["name" => $name, "level_cost" => $level_cost, "money_cost" => $money_cost, "xp_cost" => $xp_cost]))->save();
        rename($world_path . $level_name, $world_path . $name);
        Compression::copy($world_path . $level_name . DS, SkyBlock::getBackupFolder() . $level_name . DS);
        Compression::remove($world_path . $level_name);
        $sender->sendMessage("§8» §aHerşey hazır");
        return true;
    }
}