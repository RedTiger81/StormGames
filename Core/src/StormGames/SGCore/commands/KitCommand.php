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

namespace StormGames\SGCore\commands;

use jojoe77777\FormAPI\SimpleForm;
use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\command\CommandSender;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Kit\Kit;
use StormGames\Kit\KitManager;
use StormGames\Prefix;
use StormGames\SGCore\commands\RDCommand;

class KitCommand extends RDCommand{
	public function __construct(string $name){
		parent::__construct($name, 'kit');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		$this->sendMainGUI($sender);
		return true;
	}

	public function sendMainGUI(SGPlayer $player){
		$form = new SimpleForm(null);
		$form->setTitle("§8» §dKit §8«");
		$kits = [];
		foreach(KitManager::get() as $slot => $kit){
			$kits[] = $kit;
			$form->addButton(TextFormat::clean($kit->getName()));
		}
		$form->setCallable(function(SGPlayer $player, ?int $index)use($kits){
			if(is_int($index)){
				$this->sendSelectGUI($player, $kits[$index]);
			}
		});
		$form->sendToPlayer($player);
	}

	public function sendSelectGUI(SGPlayer $player, Kit $kit){
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName(Prefix::KIT());
		$menu->readonly(true);
		$inventory = $menu->getInventory();

		$inventory->setItem(23, ItemFactory::get(ItemIds::WOOL, DyeColor::LIME()->getMagicNumber())->setCustomName($player->translate("kit.select")));
		$inventory->setItem(22, ItemFactory::get(ItemIds::WOOL, DyeColor::YELLOW()->getMagicNumber())->setCustomName($player->translate("kit.back")));
		$inventory->setItem(21, ItemFactory::get(ItemIds::WOOL, DyeColor::RED()->getMagicNumber())->setCustomName($player->translate("kit.close")));

		foreach($kit->getContents() as $index => $item){
			$inventory->setItem($index, EnchantManager::addLoreForCustomEnchantment($player, $item));
		}

		$menu->setListener(function(Player $player, Item $in, Item $out, SlotChangeAction $action) use($menu, $kit){
			/** @var SGPlayer $player */
			switch($in->getMeta()){
				case DyeColor::RED()->getMagicNumber():
					$player->removeCurrentWindow();
					break;
				case DyeColor::YELLOW()->getMagicNumber():
					$player->removeCurrentWindow();
					$this->sendMainGUI($player);
					break;
				case DyeColor::LIME()->getMagicNumber():
					$player->removeCurrentWindow();
					switch($time = $kit->canClaim($player)){
						case $kit::NOT_CLAIM_PERM:
							$player->sendMessage(Prefix::KIT() . TextFormat::RED . $player->translate("kit.no.permission"));
							break;
						case $kit::CLAIM_SUCCESS:
							foreach($kit->getContents() as $item){
								$player->getInventory()->addItem(EnchantManager::addLoreForCustomEnchantment($player, $item));
							}
							$player->kitTime = strtotime("+7 days");
							$player->sendMessage(Prefix::KIT() . TextFormat::GREEN . $player->translate("kit.claim.success"));
							break;
						default:
							$player->sendMessage(Prefix::KIT() . TextFormat::YELLOW . $player->translate("kit.fail.time", [Utils::diffTime($time, $player->translate("date.format"))]));
							break;
					}
					break;
			}
		});
		$menu->send($player);
	}
}