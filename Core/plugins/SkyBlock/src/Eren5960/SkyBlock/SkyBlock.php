<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock;

use Eren5960\SkyBlock\commands\MobShopCommand;
use Eren5960\SkyBlock\generators\EndIslandGenerator;
use Eren5960\SkyBlock\generators\NetherIslandGenerator;
use Eren5960\SkyBlock\commands\GenerateIsland;
use Eren5960\SkyBlock\commands\RegisterIslandCommand;
use Eren5960\SkyBlock\commands\TestCommand;
use Eren5960\SkyBlock\commands\IslandCommand;
use Eren5960\SkyBlock\generators\VoidGenerator;
use Eren5960\SkyBlock\island\DefaultIslandManager;
use Eren5960\SkyBlock\island\Island;
use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\pass\PassManager;
use Eren5960\SkyBlock\tasks\ColorfulBeacon;
use Eren5960\SkyBlock\tasks\IslandTask;
use jasonwynn10\VanillaEntityAI\entity\AnimalBase;
use jasonwynn10\VanillaEntityAI\EntityAI;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;
use StormGames\SGCore\utils\TextUtils;
use ZipArchive;
use function count;
use function glob;
use function mkdir;

define("DS", DIRECTORY_SEPARATOR);
define("IP", "sky.stormgames.net");

class SkyBlock extends PluginBase{
    /** @var self */
    public static $api;

    public function onLoad(){
       self::$api = $this;
       @mkdir(self::getBackupFolder());
       @mkdir(self::getIslandFolder());
    }

    public function onEnable(){
        $this->loadCommands();
        $this->loadGenerators();
        $this->getScheduler()->scheduleRepeatingTask(new IslandTask(), 1);
	    $this->getScheduler()->scheduleRepeatingTask(new ColorfulBeacon(), 60);
        self::registerListener(new GlobalEvents());
        DefaultIslandManager::init();
        PassManager::init();
    }

    public function onDisable(): void{
        foreach (IslandManager::getIslands() as $island){
            IslandManager::closeIsland($island);
        }
    }

    /**
     * @return SkyBlock
     */
    public static function getAPI(): self{
        return self::$api;
    }

    public static function registerListener(Listener $listener){
        Server::getInstance()->getPluginManager()->registerEvents($listener, self::$api);
    }

    public function loadCommands(): void{
        $commands = [new TestCommand(), new RegisterIslandCommand(), new IslandCommand(), new GenerateIsland(), new MobShopCommand()];
        $this->getServer()->getCommandMap()->registerAll("skyblock", $commands);
    }

    public function loadGenerators(): void{
	    GeneratorManager::addGenerator(NetherIslandGenerator::class, "netherisland");
	    GeneratorManager::addGenerator(EndIslandGenerator::class, "endisland");
	    GeneratorManager::addGenerator(VoidGenerator::class, "void");
	    GeneratorManager::addGenerator(VoidGenerator::class, "normalisland");
    }

    /**
     * @param string $name
     * @param string $island
     * @return array
     */
    public static function getIslandData(string $name, string $island): array{
        $archive = new ZipArchive();
        $archive->open(SkyBlock::getIslandFolder() . $name . '-' . $island . '.zip');
        $data = $archive->getArchiveComment() !== false ? yaml_parse($archive->getArchiveComment()) : [];
        $archive->close();
        return $data;
    }

    public static function getIslandFolder(): string{
        return self::$api->getDataFolder() . 'player_islands' . DS;
    }

    public static function getBackupFolder(): string{
        return self::$api->getDataFolder() . 'backup_islands' . DS;
    }

    public static function getPlayerIslandLevel(Player $player): int{
        $islandLevel = 0;
        /** @var IslandBase $island */
        if(($island = IslandManager::getIslandByName($player->getName())) !== null){
            $islandLevel = $island->options->level;
        }elseif(IslandManager::isHaveIsland($player, DEFAULT_ISLAND)){
            $islandLevel = SkyBlock::getIslandData($player->getName(), DEFAULT_ISLAND)["level"];
        }
        return $islandLevel;
    }

    public static function getPlayerIslandsCount(Player $player){
        return count(glob(self::getIslandFolder() . $player->getName() . "-*.zip"));
    }
}