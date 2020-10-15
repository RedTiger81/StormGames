<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\island;

use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\SkyBlock;
use Eren5960\SkyBlock\utils\Compression;
use pocketmine\player\Player;
use RuntimeException;
use function file_exists;

class IslandManager{
    /** @var IslandBase[] */
    private static $islands = [];

    /**
     * @param string $name
     * @return IslandBase|null
     */
    public static function getIslandByName(string $name): ?IslandBase{
        return self::$islands[$name] ?? null;
    }

    /**
     * @return IslandBase[]
     */
    public static function getIslands(): array{
        return self::$islands;
    }

    /**
     * @param Player $player
     * @param string $island
     * @return IslandBase
     */
    public static function initIsland(Player $player, string $island): IslandBase{
        $name = $player->getName();
        $new = false;
        if(!IslandManager::isHaveIsland($player, $island)){
            Compression::cloneIsland($island, $name);
            $new = true;
        }
        if(isset(self::$islands[$name])){
            if(self::$islands[$name]->getName() !== $island){
                self::closeIsland(self::$islands[$name]);
            }else{
                return self::$islands[$name];
            }
        }
        try{
        	$class = DefaultIslandManager::$island_class[$island];
        	/** @var IslandBase $island */
            $island = new $class($name, $island, $new);
            $island->extractLevel();
        }catch (RuntimeException $e){
            var_dump($e->getMessage());
        }

        self::$islands[$name] = $island;
        return $island;
    }

    /**
     * @param IslandBase $island
     */
    public static function closeIsland(IslandBase $island): void{
        $island->close();
        unset(self::$islands[$island->owner]);
    }

    /**
     * @param Player $player
     * @param string $island
     * @return bool
     */
    public static function isHaveIsland(Player $player, string $island){
        return file_exists(SkyBlock::getIslandFolder() . $player->getName() . '-' . $island . ".zip");
    }

    /**
     * @param string $name
     * @param string $island
     * @return string
     */
    public static function getIslandZip(string $name, string $island): string{
        return SkyBlock::getIslandFolder() . $name . '-' . $island . '.zip';
    }

    public static function isInIsland(Player $player): bool{
    	return ($island = self::getIslandByName($player->getName())) instanceof IslandBase && $island->inIsland($player);
    }
}