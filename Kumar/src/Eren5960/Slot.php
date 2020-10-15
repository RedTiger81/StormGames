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

namespace Eren5960;

use Eren5960\SkyBlock\SkyPlayer;
use jojoe77777\FormAPI\CustomForm;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\MenuIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;

class Slot extends PluginBase{
	/** @var TaskHandler[] */
	public static $sessions = [];

	public static $items = [
		ItemIds::COAL,
		ItemIds::EMERALD,
		ItemIds::DIAMOND
	];

	public static $slots = [
		[3, 12, 21],
		[4, 13, 22],
		[5, 14, 23]
	];

	/** @var Slot */
	public static $api = null;

	public function onEnable(){
		self::$api = $this;
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$this->getServer()->getCommandMap()->register('kumar', new SlotCommand('kumar', 'Slot oyunu oyna.'));
	}

	public static function prepareBetChest(SkyPlayer $player){
		$form = new CustomForm(function(SkyPlayer $player, $data){
			if($data === null) return;
			$bet = $data["bahis"];
			if(is_numeric($bet) && ($bet = intval($bet)) >= 1000){
				if($player->getMoney() >= $bet){
					$player->reduceMoney($bet);
					self::prepare777Chest($player, $bet);
				}else{
					$player->sendMessage("§7» §e777 §coynamak için yeterli paran yok.");
				}
			}else{
				$player->sendMessage("§7» §cBahis en az §f1000TL §cgirilmelidir.");
			}
		});
		$form->setTitle("Bahis seç");
		$form->addLabel("§7Yazdığın miktar kadar bahis oynanacaktır. Min. bahis §f30TP§7'dir.");
		$form->addInput("Bahis tutarı", "30", "30", "bahis");
		$form->sendToPlayer($player);
	}

	public static function prepare777Chest(SkyPlayer $player, int $bet){
		$inv = InvMenu::create(MenuIds::TYPE_CHEST);
		self::setOtherItems($inv->getInventory());
		foreach(self::$slots as $k => $slots){
			foreach($slots as $i => $slot){
				$inv->getInventory()->setItem($slot, ItemFactory::get(self::$items[$i]));
			}
		}

		$inv->setInventoryCloseListener(function(SkyPlayer $player){
			if(isset(self::$sessions[$player->getName()])){
				$player->getInventory()->setContents(self::$sessions[$player->getName()]->getTask()->contents);
				$bet = self::$sessions[$player->getName()]->getTask()->bet;
				self::$sessions[$player->getName()]->cancel();
				unset(self::$sessions[$player->getName()]);
				$player->sendTitle('§cKaybettin', '§e' . $bet . ' TL');
			}
		});
		$inv->setListener(function(){return false;});
		$inv->readonly(true)->setName("          ----- 777 -----")->send($player);
		self::$sessions[$player->getName()] = self::$api->getScheduler()->scheduleRepeatingTask(new SlotTask($player, $inv->getInventory(), $bet, $player->getInventory()->getContents(true)), 1);
	}

	public static function setOtherItems(Inventory $inventory){
		foreach([9, 10, 11, 15, 16, 17] as $slot){
			$inventory->setItem($slot, ItemFactory::get(ItemIds::END_CRYSTAL));
		}
	}

	public static function spinSlot(Inventory $inventory, int $count){
		$slots = self::$slots[$count];
		$items = [];
		foreach($slots as $slot){
			$items[] = $inventory->getItem($slot);
		}
		$inventory->setItem($slots[0], $items[2]);
		$inventory->setItem($slots[1], $items[0]);
		$inventory->setItem($slots[2], $items[1]);
	}

	public static function spinAll(Inventory $inventory){
		for($l=0;$l<3;$l++){
			for($i=0;$i<=rand(1, 2);$i++){
				self::spinSlot($inventory, $l);
			}
		}
	}
}