<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\commands;

use Eren5960\SkyBlock\forms\Forms;
use Eren5960\SkyBlock\island\Island;
use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\SkyBlock;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class IslandCommand extends Command{

    public function __construct(){
        parent::__construct("ada", "Ada menüsü", null, []);

    }

    /**
     * @param Player|CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(IslandManager::isInIsland($sender)){
            Forms::islandSettings($sender, IslandManager::getIslandByName($sender->getName()));
            return false;
        }
        if(SkyBlock::getPlayerIslandsCount($sender) === 0){
            Forms::islandCreate($sender);
            return false;
        }
        Forms::islandTeleport($sender);
        return true;
    }
}