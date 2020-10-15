<?php
/*
 *  _____               _               ___   ___  __ 
 * /__   \___  _ __ ___| |__   /\/\    / __\ / _ \/__\
 *   / /\/ _ \| '__/ __| '_ \ /    \  / /   / /_)/_\  
 *  / / | (_) | | | (__| | | / /\/\ \/ /___/ ___//__  
 *  \/   \___/|_|  \___|_| |_\/    \/\____/\/   \__/
 *
 * (C) Copyright 2019 TorchMCPE (http://torchmcpe.fun/) and others.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Contributors:
 * - Eren Ahmet Akyol
 */
declare(strict_types=1);

namespace Eren5960\Hira;

use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Chest;
use pocketmine\block\Sign as WallSign;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityFactory;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\world\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;

class Main extends PluginBase{
	/** @var self $api */
	private static $api;

	public function onLoad(){
		self::$api = $this;
	}

	public static function get() : self{
		return self::$api;
	}

	public function onEnable(){
		EntityFactory::register(VirtualEntity::class, ["virtualEntity"]);
		$this->getServer()->getPluginManager()->registerEvents(new Events(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new ItemTask(), 20 * 2);
		$this->reloadConfig();
	}

	public static function createVirtualItem(Item $item, Position $position){
		$nbt = EntityFactory::createBaseNBT($position);
		$item->setCount(1);
		$itemNbt = $item->nbtSerialize();
		$itemNbt->setByte("ChestShop", 1);
		$nbt->setTag("Item", $itemNbt);
		$nbt->setByte("ChestShop", 1);
		$entity = EntityFactory::create(VirtualEntity::class, $position->getWorld(), $nbt);
		$entity->spawnToAll();
	}

	public static function hashBlock(Block $block) : string{
		$block = $block->getPos();
		return $block->getWorld()->getDisplayName() . ";" . $block->getX() . ";" . $block->getY() . ";" . $block->getZ();
	}

	public static function dehashPosition(string $hash) : Position{
		$r = explode(";", $hash);
		return new Position((int) $r[1], (int) $r[2], (int) $r[3], Server::getInstance()->getWorldManager()->getWorldByName($r[0]));
	}

	public static function hashItem(string $item) : ?Item{
		$item = explode(":", $item);
		if(count($item) < 2) return null;
		return ItemFactory::get((int) $item[0], (int) $item[1]);
	}

	public static function getChest(Block $block) : ?Chest{
		foreach($block->getHorizontalSides() as $side) if($side instanceof Chest) return $side;
		return null;
	}

	public static function getSign(Block $block) : ?WallSign{
		foreach($block->getHorizontalSides() as $side) if($side instanceof WallSign) return $side;
		return null;
	}

	public static function trySetItem(\pocketmine\block\tile\Chest $chest, Item $item, bool $force = false) : ?Item{
		if($force || ($item = self::getItemInChest($chest->getInventory(), $item)) !== null){
			self::createVirtualItem($item, Position::fromObject($chest->getPos()->add(0.5, 1, 0.5), $chest->getPos()->getWorld()));
			return $item;
		}
		return null;
	}

	private static $signConfig = null;

	public static function getSignConfig() : Config{
		if(self::$signConfig === null){
			self::$signConfig = new Config(self::get()->getDataFolder() . "signs.yml", Config::YAML);
		}

		return self::$signConfig;
	}

	private static $chestConfig = null;

	public static function getChestConfig() : Config{
		if(self::$chestConfig === null){
			self::$chestConfig = new Config(self::get()->getDataFolder() . "chests.yml", Config::YAML);
		}

		return self::$chestConfig;
	}

	public static function getItemInChest(ChestInventory $inventory, Item $item, bool $checkCompound = false) : ?Item{
		foreach($inventory->getContents() as $content) if($content->equals($item, false, $checkCompound)) return $content;
		return null;
	}

	public static function clearVirtuals(Position $position) : void{
		for($i = 0; $i <= 3; $i++){
			if(($entity = self::getVirtual($position)) !== null) $entity->flagForDespawn();
		}
	}

	public static function getItemCount(Item $item, Inventory $inventory) : int{
		$count = 0;
		$checkDamage = !$item->hasAnyDamageValue();
		foreach($inventory->getContents(false) as $index => $i){
			if($item->equals($i, $checkDamage, true)){
				$count += $i->getCount();
			}
		}

		return $count;
	}

	public static function getVirtual(Position $position) : ?VirtualEntity{
		return $position->getWorld()->getNearestEntity($position, 2, VirtualEntity::class);
	}

	public static $cache = ["Cobblestone" => "Kırıktaş"];

	public static function translateText(string $sourceLang, string $targetLang, string $text) : string{
		if(isset(self::$cache[$text])) return self::$cache[$text];
		return self::$cache[$text] = json_decode(Internet::getURL("https://translate.googleapis.com/translate_a/single?client=gtx&sl=$sourceLang&tl=$targetLang&dt=t&ie=UTF-8&oe=UTF-8&q=" . urlencode($text)))[0][0][0] ?? $text;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(!$sender instanceof SkyPlayer || !$command->testPermissionSilent($sender)) return false;
		if(isset($args[0]) && $args[0] === "temizle" && $sender->isOp()){
			foreach(Server::getInstance()->getWorldManager()->getWorlds() as $level){
				foreach($level->getTiles() as $tile){
					if($tile instanceof \pocketmine\block\tile\Chest){
						$level->setBlock($tile->getBlock()->getPos(), BlockFactory::get(0));
						$tile->close();
					}
				}
				foreach($level->getEntities() as $entity){
					if($entity instanceof VirtualEntity){
						$entity->flagForDespawn();
					}
				}
			}
			$sender->sendMessage(" Silindi");
		}
		if(isset($args[0]) && $args[0] === "test" && $sender->isOp()){
			$item = clone $sender->getInventory()->getItemInHand();
			$item->setCustomName($args[1] ?? "Eren Ahmed Akyol");
			$sender->getInventory()->remove($sender->getInventory()->getItemInHand());
			$sender->getInventory()->addItem($item);
		}
		return true;
	}
}