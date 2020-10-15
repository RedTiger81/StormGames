<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 01 Nisan 2020
 */
declare(strict_types=1);
 
namespace Eren5960\Mini_me;
 
use Eren5960\Mini_me\entity\MiniMe;
use Eren5960\SkyBlock\SkyPlayer;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class MCmd extends Command{
	public const COINS = 15;
	public const COST = "\n§8» §3" . self::COINS . " Nakit §8«";

	public function __construct(){
		parent::__construct("çırak", "Köle değil çırak!", "/çırak", ["cirak", "mme"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof SkyPlayer){
			self::mainForm($sender);
		}
	}

	public static function mainForm(SkyPlayer $player): void{
		$form = new SimpleForm(function(SkyPlayer $player, ?int $index){
			if($index === null) return;
			if($index === 0) self::madenCirak($player);
			if($index === 1) self::ciftciCirak($player);
			if($index === 2) self::oduncuCirak($player);
		});
		$form->setTitle("Çırak menü");
		$form->setContent("§7Merhaba, şimdilik 3 adet çırağımız var. Bunları sana yardımcı olması için tasarladık. Lütfen onlara kötü davranma.");
		$prefix = "\n§8» §2Detaylar §8«";
		$form->addButton("Madenci" . $prefix, 1, '');
		$form->addButton("Çiftçi" . $prefix, 1, '');
		$form->addButton("Oduncu" . $prefix, 1, '');
		$form->sendToPlayer($player);
	}

	public static function oduncuCirak(SkyPlayer $player): void{
		$form = new SimpleForm(function(SkyPlayer $player, ?int $index){
			if($index === null || $index === 1){self::mainForm($player);return;}
			self::control($player, 'Oduncu');
		});
		$form->setTitle("Oduncu Çırak");
		$form->setContent("§7Selam! Ben oduncu çırak. Senin için ağaç kesip yetiştirim ve envanterimde saklarım. Bunu severek yapacağıma söz veriyorum!");
		$form->addButton("§2SATIN AL" . self::COST);
		$form->addButton("< Geri dön");
		$form->sendToPlayer($player);
	}

	public static function madenCirak(SkyPlayer $player): void{
		$form = new SimpleForm(function(SkyPlayer $player, ?int $index){
			if($index === null || $index === 1){self::mainForm($player);return;}
			self::control($player, 'Madenci');
		});
		$form->setTitle("Madenci Çırak");
		$form->setContent("§7Selam! Ben madenci çırak. Senin için önüme çıkan blokları kırarım ve enventerimde saklarım. Bunu severek yapacağıma söz veriyorum!");
		$form->addButton("§2SATIN AL" . self::COST);
		$form->addButton("< Geri dön");
		$form->sendToPlayer($player);
	}

	public static function ciftciCirak(SkyPlayer $player): void{
		$form = new SimpleForm(function(SkyPlayer $player, ?int $index){
			if($index === null || $index === 1){self::mainForm($player);return;}
			self::control($player, 'Ciftci');
		});
		$form->setTitle("Çiftçi Çırak");
		$form->setContent("§7Selam! Ben oduncu çırak. Senin için ekinleri ekip biçerim ve bunları envanterimde saklarım. Bunu severek yapacağıma söz veriyorum!");
		$form->addButton("§2SATIN AL" . self::COST);
		$form->addButton("< Geri dön");
		$form->sendToPlayer($player);
	}

	public static function getItem(string $type, string $owner, ?MiniMe $miniMe = null): Item{
		$item = ItemFactory::get(ItemIds::GLOWING_OBSIDIAN);
		$nbt = $item->getNamedTag();
		$nbt->setString(Main::NBTP . "type", $type = strtolower($type));
		$nbt->setString(Main::NBTP . "owner", $owner);
		$add = "";

		if($miniMe !== null){
			$add = " §7(§b" . $miniMe->level . "§7)";
			$nbt->setInt(Main::NBTP . 'created', $miniMe->created);
			$nbt->setInt(Main::NBTP . 'level', $miniMe->level);
			$nbt->setString(Main::NBTP . 'type', $miniMe->type);
			$nbt->setTag(Main::NBTP . 'Inventory', $miniMe->getTagOfInventory());
		}
		$item->setNamedTag($nbt);
		$item->setCustomName(Main::NAMES[$type] . $add . "\n§7Yerleştirmek istediğin yere koy.");
		return $item;
	}

	public static function control(SkyPlayer $player, string $type): void{
		$item = self::getItem($type, $player->getName());
		if($player->getInventory()->canAddItem($item)){
			if($player->getCoins() >= self::COINS){
				$player->reduceMoney(self::COINS);
				$player->getInventory()->addItem($item);
				$player->sendAlert("BAŞARILI", "§aEnvanterine bir blok eşyası(parlayan obsidyen) koyduk. Bunu istediği yere yerleştir ve çırağın hazır! Korkma, dilersen çırağa dokunup yerini değiştirebilirsin.", "Tamam", "< Ana menü", null, function()use($player){self::mainForm($player);});
			}else{
				$player->sendAlert("HATA", "Yeterli nakitin yok! Web Panelden nakit satın alabilirsin.\n§8» §bstormgames.net", "Hemen alıyorum", "X Kapat");
			}
		}else{
			$player->sendAlert("HATA", "Envanterinde yeteri kadar alan yok.", "Hemen yer açıyorum", "X Kapat");
		}
	}
}