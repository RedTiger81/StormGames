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
use jojoe77777\FormAPI\SimpleForm;
use muqsit\invmenu\InvMenu;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Nutella extends Command{

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof SkyPlayer){
			$form = new SimpleForm(function(SkyPlayer $player, ?int $choice){
				if($choice === 0){
					if($player->getMoney() >= 3000){
						$player->reduceMoney(3000, false);
						$this->initGUI($player);
					}else{
						$player->sendMessage("§7» §cYeterli paraya sahip değilsin.");
					}
				}
			});
			$form->setTitle("Onaylama Ekranı");
			$form->setContent("§fKazı Kazan §7oynamak için §f3000 TL §7harcayacaksın.");
			$form->addButton("Onayla");
			$form->addButton("Kapat");
			$form->sendToPlayer($sender);
		}
	}

	public function initGUI(SkyPlayer $player){
		Pirana::$session[$player->getName()] = 0;
		$inv = InvMenu::create(InvMenu::TYPE_CHEST);
		$inv->readonly();
		$inv->setName("Tıkla kazı!");

		for($i=0;$i<$inv->getInventory()->getSize();$i++){
			$inv->getInventory()->setItem($i, ItemFactory::get(ItemIds::CHEST)->setCustomName("§aKazı"));
		}

		$inv->setListener(function(SkyPlayer $player, Item $source, Item $target, SlotChangeAction $action){
			$inv = $action->getInventory();
			if(is_int(Pirana::$session[$player->getName()]) && $source->getId() === ItemIds::CHEST){
				if(Pirana::$session[$player->getName()] === 2){
					$inv->setItem($action->getSlot(), Pirana::rand());
					Pirana::$session[$player->getName()] = [];
					foreach($inv->getContents(true) as $index => $item){
						if($item->getId() === ItemIds::CHEST){
							$inv->setItem($index, ItemFactory::get(ItemIds::END_CRYSTAL)->setCustomName("§dOyun bitti"));
						}else{
							Pirana::$session[$player->getName()][] = Pirana::$data[$item->getId()];
						}
					}
				}else{
					Pirana::$session[$player->getName()]++;
					$inv->setItem($action->getSlot(), Pirana::rand());
				}
			}
			return false;
		});

		$inv->setInventoryCloseListener(function(SkyPlayer $player){
			if(!is_array(Pirana::$session[$player->getName()])){
				$player->sendMessage("§7» §cOyun kapatıldığı için kaybettin");
			}else{
				$v = Pirana::$session[$player->getName()];
				$money = $v[0] + $v[1] + $v[2];
				$player->sendMessage("§7» §e" . $money . " TL §ahesabına aktrıldı.");
				$player->addMoney($money);
			}
			unset(Pirana::$session[$player->getName()]);
		});
		$inv->send($player);
	}
}