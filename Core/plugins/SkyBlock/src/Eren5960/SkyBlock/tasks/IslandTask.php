<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\tasks;

use Eren5960\SkyBlock\island\IslandManager;
use pocketmine\scheduler\Task;

class IslandTask extends Task{
    public function onRun($currentTick){
        foreach (IslandManager::getIslands() as $island){
            $island->onTick($currentTick);
        }
    }
}