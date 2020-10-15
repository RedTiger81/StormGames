<?php

declare(strict_types=1);

namespace Eren5960\Hira;

use Eren5960\SkyBlock\SkyPlayer;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Sign;
use pocketmine\block\tile\Chest;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Server;
use StormGames\SGCore\utils\Utils;

class Events implements Listener{
	public function onSignChange(SignChangeEvent $event){
		$block = $event->getBlock();
		$lines = $event->getNewText()->getLines();
		if(!$event->getBlock() instanceof Sign) return;
		if($event->getNewText()->getLine(0) !== "pazar") return;

		if(is_numeric($lines[2])){
			if(($item = Main::hashItem($lines[1])) === null) return;
			if(($chest = Main::getChest($block)) === null) return;

			$c = Main::getSignConfig();
			$c->set($blockHash = Main::hashBlock($block), [
				"item" => $lines[1],
				"money" => $cost = intval($lines[2]),
				"name" => $name = Main::translateText("eng", "tr", $item->getName()),
				"chest" => $chestHash = Main::hashBlock($chest),
				"owner" => $owner = $event->getPlayer()->getName()
			]);
			$c->save();

			$c = Main::getChestConfig();
			$c->set($chestHash, $blockHash);
			$c->save();
			/** @var Chest $tile */
			$tile = $event->getPlayer()->getWorld()->getTile($chest->getPos());
			$tile->setName("Pazar:" . $item->getId());

			$c = Main::get()->getConfig();
			$ts = function(int $id)use($cost, $owner, $name, $c){return str_replace(["{cost}", "{pb}", "{owner}", "{item_name}"], [$cost, "$", $owner, $name], $c->get("line-{$id}"));};
			$event->getNewText()->setLines([$ts(1), $ts(2), $ts(3), $ts(4)]);
		}
	}

	public function onBreak(BlockBreakEvent $event){
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($block->getId() === BlockLegacyIds::CHEST){
			$c = Main::getChestConfig();
			if($c->exists($hash = Main::hashBlock($block))){
				$signhash = $c->get($hash);
				$c1 = Main::getSignConfig();
				if($c1->get($signhash)["owner"] !== $player->getName()){
					$event->setDrops([]);
					$event->setCancelled();
					return;
				}
				$c->remove($hash);$c1->remove($signhash);
				$c->save();$c1->save();

				Main::clearVirtuals($block->getPos());
				$block->getPos()->getWorld()->useBreakOn(Main::dehashPosition((string) $signhash), $item);
				$player->sendMessage("§7» §cPazar kaldırıldı.");
			}
		}elseif($block instanceof Sign){
			$c = Main::getSignConfig();
			if($c->exists($hash = Main::hashBlock($block))){
				$chesthash = $c->get($hash)["chest"];
				if($c->get($hash)["owner"] !== $player->getName()){
					$event->setDrops([]);
					$event->setCancelled();
					return;
				}
				$c->remove($hash);
				$c->save();
				Main::clearVirtuals($block->getPos());
				$block->getPos()->getWorld()->useBreakOn(Main::dehashPosition((string) $chesthash), $item);
				$c = Main::getChestConfig();
				$c->remove($chesthash);
				$c->save();
				$player->sendMessage("§7» §cPazar kaldırıldı.");
			}
		}
	}

	public function onClick(PlayerInteractEvent $event){
		$block = $event->getBlock();
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		if(!$block instanceof Sign) return;

		if(($data = Main::getSignConfig()->get(Main::hashBlock($block), false)) !== false){
			/*if($data["owner"] === $player->getName()){
				$player->sendMessage("§7» §cKendi pazarından satın alamazsın!");
				return;
			}*/
			/** @var Chest $tile */
			$tile = $block->getPos()->getWorld()->getTile(Main::dehashPosition($data["chest"]));
			if(!$tile instanceof Chest) return;

			/** @var VirtualEntity $virtual */
			$virtual = Main::getVirtual($tile->getInventory()->getHolder());
			if($virtual === null){
				$player->sendMessage("§7» §7Sandık boş ve ya yükleniyor olabilir 3sn içinde tekrar deneyin.");
				return;
			}else{
				$virtual = $virtual->getItem();
			}

			$totalItem = Main::getItemCount($virtual, $tile->getInventory());
			$form = new CustomForm(function(SkyPlayer $player, ?array $response = null) use($data, $tile, $virtual){
				if($response === null || $tile->isClosed() or Main::getItemInChest($tile->getInventory(), $virtual, true) === null) return;
				$count = array_pop($response);

				$totalItem = Main::getItemCount($virtual, $tile->getInventory());
				if($totalItem <= 0) return;

				if(is_numeric($count) && ($count = intval($count)) > 0){
					if($count > $totalItem){
						$player->sendMessage("§7» §cPazarda yeteri kadar item yok.");
						return;
					}
					$cost = $count * $data["money"];
					if($player->getMoney() >= $cost){
						$player->reduceMoney($cost);
						$item = clone $virtual;
						$item->setCount($count);
						$item->setCustomName($virtual->getCustomName());
						$tile->getInventory()->removeItem($item);
						$player->getInventory()->addItem($item);
						$player->sendMessage("§7» §e" . $count . " §aadet §e" . $item->getName() . " §abaşarıyla satın alındı.");
						if(($owner = Server::getInstance()->getPlayerExact($data["owner"])) instanceof SkyPlayer){
							$owner->addMoney($cost);
							$owner->sendMessage("§7» §e" . $player->getName() . " §7pazarınızdan §f" . $count . "§7 adet §f" . $item->getName() . "§7 alarak size §a" . Utils::addMonetaryUnit($cost) . "§7 kazandırdı.");
						}
					}else{
						$player->sendMessage("§7» §cBiraz daha paraya ihtiyacın var.");
					}
				}else{
					$player->sendMessage("§7» §cLütfen sayısal bir değer girin.");
				}
			});
			$form->setTitle($data["owner"] . " Pazarı");
			$form->addLabel("§3Mevcut para: §f" . Utils::addMonetaryUnit($player->getMoney()));
			$form->addLabel("§3Satın alınacak item: §f" . ($virtual->hasCustomName() ? $virtual->getCustomName() : $data["name"]));
			if($virtual->hasEnchantments()){
				$form->addLabel("§3Büyüler: §f". implode(", ", array_map(function(EnchantmentInstance $ench){return $ench->getType()->getName() . " " . $ench->getLevel() . ". Seviye";}, $virtual->getEnchantments())));
			}
			$form->addLabel("§3Pazarda mevcut adet: §f" . $totalItem);
			$form->addLabel("§3Adet başına fiyat: §f" . Utils::addMonetaryUnit($data["money"]));
			$form->addInput("§3Kaç adet satın almak istersin?", "1", "1");
			$form->sendToPlayer($player);
		}
	}
}