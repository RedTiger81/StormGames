<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use Eren5960\SkyBlock\generators\VoidGenerator;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\world\format\io\data\BedrockWorldData;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\TextUtils;
use StormGames\SGCore\utils\Utils;

class WorldCommand extends Command{
	public function __construct(string $name){
		parent::__construct($name, "World manager for StormGames");

		$this->setPermission(DefaultPermissions::ADMIN);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof ConsoleCommandSender){
			if(!empty($args)){
				$name = implode(' ', $args);
				$manager = Server::getInstance()->getWorldManager();
				if($manager->isWorldGenerated($name) && $manager->getWorldByName($name) === null) $manager->loadWorld($name, true);
			}else{
				$sender->sendMessage("/{$this->getName()} [isim]>");
			}
		}
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if(empty($args)){
			throw new InvalidArgumentException();
		}

		$world = $sender->getWorld();
		switch(array_shift($args)){
			case "fixname":
				/** @var BedrockWorldData $data */
				$data = $world->getProvider()->getWorldData();
				$data->getCompoundTag()->setString("LevelName", $world->getFolderName());
				$sender->sendMessage("§aDeğişiklikleri kaydetmek için sunucuyu yeniden başlat.");
				break;
			case "tp":
				if(!empty($args)){
					$world = implode(' ', $args);
					$level = Utils::getWorldByName($world);
					if($level !== null){
						$sender->teleport($level->getSpawnLocation());
						$sender->sendMessage(TextFormat::GREEN . "$world isimli dünyaya ışınlandın!");
					}else{
						$sender->sendMessage(TextFormat::RESET . "$world isimli dünya bulunamadı! Dünya listesine bakmak için /{$this->getName()} list");
					}
				}else{
					$sender->sendMessage("/{$this->getName()} tp [isim]>");
				}
				break;
			case "list":
				$list = '';
				foreach(scandir($sender->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR, SCANDIR_SORT_NONE) as $worldFolder){
					if($worldFolder === '.' or $worldFolder === '..') continue;

					$list .= TextFormat::EOL . TextFormat::WHITE . $worldFolder . TextFormat::GRAY . " - " . ($sender->getServer()->getWorldManager()->isWorldLoaded($worldFolder) ? "§aYÜKLENDİ" : "§cYÜKLENMEDİ");
				}

				$sender->sendMessage("Dünya Listeleri $list");
				break;
			case "create":
				if(!empty($args)){
					$world = $args[0];
					if($sender->getServer()->getWorldManager()->isWorldGenerated($world)){
						$sender->sendMessage(TextFormat::RED . "$world isimli dünya zaten var!");
					}else{
						$generator = $args[1] ?? "default";
						$generator = GeneratorManager::getGenerator($generator);
						$sender->getServer()->getWorldManager()->generateWorld($world, $args[2] ?? 1000, $generator);
						$sender->sendMessage(TextFormat::GREEN . "$world isimli dünya oluşturuldu!");
					}
				}else{
					$sender->sendMessage("/{$this->getName()} create [isim]");
				}
				break;
			case "void":
				if(!empty($args)){
					$world = $args[0];
					if($sender->getServer()->getWorldManager()->isWorldGenerated($world)){
						$sender->sendMessage(TextFormat::RED . "$world isimli dünya zaten var!");
					}else{
						Server::getInstance()->getWorldManager()->generateWorld($world, null, VoidGenerator::class);
						$sender->sendMessage(TextFormat::GREEN . "$world isimli dünya oluşturuldu!");
					}
				}else{
					$sender->sendMessage("/{$this->getName()} void [isim]");
				}
				break;
			case "kill":
				$entCount = 0;
				$tileCount = 0;
				foreach($world->getEntities() as $ent){
					if($ent instanceof SGPlayer){
						continue;
					}

					$ent->close();
					++$entCount;
				}
				foreach($world->getChunks() as $chunk){
					foreach($chunk->getTiles() as $tile){
						$tile->close();
						++$tileCount;
					}
				}
				$sender->sendMessage("$entCount entity ve $tileCount tile kapatıldı!");
				break;
			case "kill-tile":
				$tileCount = 0;
				$saveId = $args[0] ?? null;
				foreach($world->getChunks() as $chunk){
					foreach($chunk->getTiles() as $tile){
						if($saveId === TextUtils::classStringToName(get_class($tile))){
							$tile->close();
							++$tileCount;
						}
					}
				}
				$sender->sendMessage("$tileCount tile kapatıldı!");
				break;
			case "kill-entity":
				$entCount = 0;
				$saveId = $args[0] ?? null;
				foreach($world->getChunks() as $chunk){
					foreach($chunk->getEntities() as $entity){
						if($entity instanceof SGPlayer) continue;

						if($saveId === null || $saveId === TextUtils::classStringToName(get_class($entity))){
							$entity->close();
							++$entCount;
						}
					}
				}
				$sender->sendMessage("$entCount entity kapatıldı!");
				break;
			case "save":
				$mode = array_shift($args);
				if($mode === null){
					$world->save(true);
					$sender->sendMessage("Kaydedildi.");
				}else{
					$world->setAutoSave((bool) $mode);
					$sender->sendMessage("Kaydetme durumu ayarlandı. (" . $mode . ")");
				}
				break;
			case "dump":
				$entCount = 0;
				$tiles = [];
				$ent = [];
				$chunkCount = 0;
				foreach($world->getChunks() as $chunk){
					++$chunkCount;
					foreach($chunk->getTiles() as $tile){
						if(!isset($tiles[$tile->getBlock()->getName()])){
							$tiles[$tile->getBlock()->getName()] = 0;
						}
						$tiles[$tile->getBlock()->getName()]++;
					}
					foreach($chunk->getEntities() as $entity){
						if($entity instanceof SGPlayer){
							continue;
						}
						if(!isset($ent[$str = TextUtils::classStringToName(get_class($entity))])){
							$ent[$str] = 0;
						}
						$ent[$str]++;
						++$entCount;
					}
				}
				$sender->sendMessage("---- WORLD DUMP ----");
				$sender->sendMessage("Tiles: ");
				foreach($tiles as $tile => $count){
					$sender->sendMessage($tile . " : " . $count . " tane");
				}
				$sender->sendMessage("Entity: ");
				foreach($ent as $entity => $count){
					$sender->sendMessage($entity . " : " . $count . " tane");
				}
				$sender->sendMessage("Chunks: " . $chunkCount);
				$sender->sendMessage("---- WORLD DUMP ----");
				break;
		}

		return true;
	}
}