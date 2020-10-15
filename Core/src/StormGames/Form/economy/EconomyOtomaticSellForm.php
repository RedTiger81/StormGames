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

namespace StormGames\Form\economy;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\SharedInvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use StormGames\SGCore\SGPlayer;

class EconomyOtomaticSellForm{

	public function __construct(Player $player){
		self::send($player);
	}

	public static function send(SGPlayer $player){
		$menu = self::prepareChest($player);
		$menu->setListener(function(SGPlayer $player, Item $in, Item $out, SlotChangeAction $action) use($menu){
			$id = $in->getId();
			if($id === ItemIds::GLASS_PANE) return;
			if(in_array($id, $player->otoSellIds)){
				unset($player->otoSellIds[array_search($id, $player->otoSellIds)]);
				$menu->getInventory()->setItem($action->getSlot(), $in->setCustomName("§c" . $player->translate('forms.economy.otoSell.disable')));
			}else{
				$player->otoSellIds[] = $id;
				$menu->getInventory()->setItem($action->getSlot(), $in->setCustomName("§a" . $player->translate('forms.economy.otoSell.enable')));
			}
		});

		EconomyCoinShopForm::globalCloseEvent($menu);
		$menu->send($player);
	}

	public static function prepareChest(SGPlayer $player): SharedInvMenu{
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName("Otomatik item satma paneli");
		$menu->readonly(true);
		$inventory = $menu->getInventory();

		for($i=0;$i<$inventory->getSize();$i++){
			$inventory->setItem($i, ItemFactory::get(ItemIds::GLASS_PANE, DyeColor::BLACK()->getMagicNumber()));
		}

		$z = 0;
		foreach(EconomySellForm::ITEMS as $itemId => $money){
			$inventory->setItem($z++,
				ItemFactory::get($itemId
				)->setCustomName(in_array($itemId, $player->otoSellIds) ? ("§a" . $player->translate('forms.economy.otoSell.enable')) : ("§c" . $player->translate('forms.economy.otoSell.disable')))
				->setLore(["Tanesi : " . $money . "$"])
			);
		}
		return $menu;
	}
}