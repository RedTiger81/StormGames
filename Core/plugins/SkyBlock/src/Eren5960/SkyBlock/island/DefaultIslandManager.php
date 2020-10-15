<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\island;

use Eren5960\SkyBlock\island\island\EndIsland;
use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\island\island\NetherIsland;
use Eren5960\SkyBlock\island\island\NormalIsland;
use Eren5960\SkyBlock\SkyBlock;
use pocketmine\utils\Config;
use pocketmine\utils\Terminal;
use function array_keys;
use function array_values;

define("DEFAULT_ISLAND", "Normal Ada");
define("NETHER_ISLAND", "Nether Ada");
define("END_ISLAND", "End Ada");

class DefaultIslandManager{
    /** @var Config[] */
    private static $islands = [];
	public static $island_class = [];

    public static function init(): void{
    	self::registerIsland(NormalIsland::class, SkyBlock::getBackupFolder() . DEFAULT_ISLAND . DS . 'config.yml');
	    self::registerIsland(NetherIsland::class, SkyBlock::getBackupFolder() . NETHER_ISLAND . DS . 'config.yml');
	    self::registerIsland(EndIsland::class, SkyBlock::getBackupFolder() . END_ISLAND . DS . 'config.yml');
    }

    public static function registerIsland(string $class, string $config_path){
        $config = new Config($config_path, 2);

        self::$islands[$class] = $config;
        self::$island_class[$config->get('name')] = $class;
        echo Terminal::$COLOR_GOLD . $config->get('name') . Terminal::$COLOR_GREEN . " yüklendi!" . PHP_EOL;
    }

    public static function getIslands(): array{
        $data = [];
        foreach(self::$island_class as $name => $class){
        	$data[] = $name;
        }
        return $data;
    }

    public static function getIslandData(string $class): array{
        return self::$islands[$class]->getAll();
    }

    public static function getIslandsData(): array{
    	$data = [];
    	foreach(self::$islands as $class => $data_){
    		$data[$data_->get('name')] = $data_;
	    }
    	return $data;
    }
}