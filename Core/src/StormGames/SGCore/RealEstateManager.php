<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\SkyPlayer;
use muqsit\worldstyler\schematics\async\AsyncSchematic;
use muqsit\worldstyler\schematics\Schematic;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use function glob;
use function str_replace;
use function explode;
use function basename;
use function array_keys;

class RealEstateManager{
    /** @var Schematic[][][] */
    private static $properties = [];
    /** @var array */
    private static $queue = [];

    public static function init() : void{
        $dir = SGCore::getAPI()->getResourcesDir() . 'estate' . DIRECTORY_SEPARATOR;
        foreach(glob($dir . '*', GLOB_ONLYDIR) as $dirName){
            self::loadProperties($dirName, str_replace($dir, '', $dirName));
        }
    }

    private static function loadProperties(string $dir, string $name) : void{
        $dir = $dir . DIRECTORY_SEPARATOR;
        foreach(glob($dir . '*.schematic', GLOB_NOSORT) as $dirName){
            list($propertyName, $price) = explode('-', str_replace($dir, '', $dirName), 2);
            $schematic = new AsyncSchematic($dirName);
            $schematic->load();
            self::$properties[$name][$propertyName] = [$schematic, (int) basename($price, '.schematic')];
        }
    }

    public static function getCategories() : array{
        return array_keys(self::$properties);
    }

    public static function getPropertiesByCategory(string $name) : array{
        return array_keys(self::$properties[$name]);
    }

    public static function getProperty(string $category, string $name) : array{
        return self::$properties[$category][$name];
    }

	private static function getSchematicFromItem(Item $item) : Schematic{
		list($category, $name) = explode('.', $item->getNamedTag()->getString('estate'), 2);
		return self::$properties[$category][$name][0];
	}

	public static function startPaste(SkyPlayer $player, Block $block, Item $item) : void{
          if(!isset(self::$queue[$player->getId()])){
		       if(self::canChange($player)){
			       $player->sendAlert("Onaylıyor musun?", "Bunu onayladığın taktirde elindeki emlak bloklarını adana yapıştıracaksın.", "Onayla", "X Kapat", function()use($player, $block, $item){
				       self::$queue[$player->getId()] = microtime(true);
				       self::getSchematicFromItem($item)->paste($block->getPos()->getWorld(), $block->getPos()->add(0, 1));
				       $player->getInventory()->clear($player->getInventory()->getHeldItemIndex());
				       $player->sendMessage(Prefix::RealEstate() . $player->translate('forms.realEstate.success', [TextFormat::WHITE . round(microtime(true) - self::$queue[$player->getId()], 3) . TextFormat::GRAY]));
				       unset(self::$queue[$player->getId()]);
			       });
		       }
          }else{
	          $player->sendMessage(Prefix::RealEstate() . TextFormat::RED . $player->translate('forms.realEstate.error'));
          }
    }

    private static function canChange(SGPlayer $player) : bool{
        $island = IslandManager::getIslandByName($player->getName());
        if($island === null){
        	$player->sendMessage("§7» §cBunu kullanmak için adada olmalısın.");
        	return false;
        }elseif(!$island->isOwner($player)){
	        $player->sendMessage("§7» §cBunu kullanmak için ada sahibi olmalısın.");
	        return false;
        }
        return true;
    }
}