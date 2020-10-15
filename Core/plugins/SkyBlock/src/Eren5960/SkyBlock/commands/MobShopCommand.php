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
 * @date 30 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\commands;
 
use Eren5960\SkyBlock\SkyPlayer;
use jasonwynn10\VanillaEntityAI\entity\hostile\Creeper;
use jasonwynn10\VanillaEntityAI\entity\hostile\Drowned;
use jasonwynn10\VanillaEntityAI\entity\hostile\Skeleton;
use jasonwynn10\VanillaEntityAI\entity\passive\Chicken;
use jasonwynn10\VanillaEntityAI\entity\passive\Cow;
use jasonwynn10\VanillaEntityAI\entity\passive\Llama;
use jasonwynn10\VanillaEntityAI\entity\passive\Mooshroom;
use jasonwynn10\VanillaEntityAI\entity\passive\Pig;
use jasonwynn10\VanillaEntityAI\entity\passive\Sheep;
use jasonwynn10\VanillaEntityAI\entity\passiveaggressive\IronGolem;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Zombie;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use StormGames\SGCore\utils\TextUtils;
use StormGames\SGCore\utils\Utils;

class MobShopCommand extends Command{
	public static $animals = [
		Llama::class => ['link' => '', 'money' => 60000],
		Cow::class => ['link' => '', 'money' => 50000],
		Mooshroom::class => ['link' => '', 'money' => 50000],
		Sheep::class => ['link' => '', 'money' => 50000],
		Pig::class => ['link' => '', 'money' => 45000],
		Chicken::class =>['link' => '',  'money' => 30000]
	];

	public static $monsters = [
		IronGolem::class => ['link' => '', 'money' => 150000],
		Zombie::class => ['link' => '', 'money' => 100000],
		Skeleton::class => ['link' => '', 'money' => 750000],
		Creeper::class => ['link' => '', 'money' => 50000],
		Drowned::class => ['link' => '', 'money' => 50000]
	];

	public function __construct(){
		parent::__construct("mob", "Hayvan ve canavar yumurtası komutu.");
	}


	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$form = new ModalForm(function (SkyPlayer $player, ?bool $index){
			if($index === null) return;
			if($index){
				$this->animalForm($player);
			}else{
				$this->monsterForm($player);
			}
		});
		$form->setTitle("Mob paneli");
		$form->setContent("Dilersen sitemizden bu paketleri toplu bir şekilde alabilirsin. Butonları kullanarak sıradaki menüyü aç.");
		$form->setButton1("Hayvan yumurtaları");
		$form->setButton2("Yaratık yumurtaları");
		$form->sendToPlayer($sender);
	}

	public function animalForm(SkyPlayer $player): void{
		$form = new SimpleForm(function(SkyPlayer $player, ?int $index){
			if($index === null) return;
			if($index === count(self::$animals)){
				if($player->getCoins() >= 5){
					$player->setCoins($player->getCoins() - 5, true);
					$player->getInventory()->addItem(ItemFactory::get(ItemIds::MOB_SPAWNER));
					$player->sendAlert("Başarılı", "Yumurta envanterine eklendi.", "< Geri Dön", "X Kapat", function()use($player){$this->animalForm($player);});
				}else{
					$player->sendAlert("Hata", "Yeterli paran yok. Dilersen sitemizden oyun parası alabilirsin. Yada mob paketlerini hazır bir şekilde satın alabilirsin.\n»§b stormgames.net", "Tamam", "X Kapat");
				}
			}else{
				$money = array_values(self::$animals)[$index]['money'];
				if($player->getMoney() >= $money){
					$player->reduceMoney($money);
					$class = array_keys(self::$animals)[$index];
					$player->getInventory()->addItem(ItemFactory::get(ItemIds::SPAWN_EGG, $class::NETWORK_ID));
					$player->sendAlert("Başarılı", "Yumurta envanterine eklendi.", "< Geri Dön", "X Kapat", function()use($player){$this->animalForm($player);});
				}else{
					$player->sendAlert("Hata", "Yeterli paran yok. Dilersen sitemizden oyun parası alabilirsin. Yada mob paketlerini hazır bir şekilde satın alabilirsin.\n»§b stormgames.net", "Tamam", "X Kapat");
				}
			}
		});
		$form->setTitle("Hayvan Yumurtaları");
		$form->setContent("§7Buradaki hayvan yumurtalarını satın aldıktan sonra ister oluştur, istersen mob spawnera yerleştir. Mob spawner'ı sitemizden satın alabilirsin.\n\nCanavarlar oluşturup onları öldürüp XP ve item kazanabilirsin.");
		foreach(self::$animals as $class => $data){
			$form->addButton(TextUtils::classStringToName($class) . "\n§8» §2" . Utils::addMonetaryUnit($data['money']) . " §8«", 0, $data['link'] !== '' ? $data['link'] : 'textures/items/spawn_egg');
		}
		$form->addButton("Spawner\n§8» §35 Nakit §8«", 0, 'textures/blocks/mob_spawner');
		$form->sendToPlayer($player);
	}

	public function monsterForm(SkyPlayer $player): void{
		$form = new SimpleForm(function(SkyPlayer $player, ?int $index){
			if($index === null) return;
			if($index === count(self::$monsters)){
				if($player->getCoins() >= 5){
					$player->setCoins($player->getCoins() - 5, true);
					$player->getInventory()->addItem(ItemFactory::get(ItemIds::MOB_SPAWNER));
					$player->sendAlert("Başarılı", "Yumurta envanterine eklendi.", "< Geri Dön", "X Kapat", function()use($player){$this->monsterForm($player);});
				}else{
					$player->sendAlert("Hata", "Yeterli paran yok. Dilersen sitemizden oyun parası alabilirsin. Yada mob paketlerini hazır bir şekilde satın alabilirsin.\n»§b stormgames.net", "Tamam", "X Kapat");
				}
			}else{
				$money = array_values(self::$monsters)[$index]['money'];
				if($player->getMoney() >= $money){
					$player->reduceMoney($money);
					$class = array_keys(self::$monsters)[$index];
					$player->getInventory()->addItem(ItemFactory::get(ItemIds::SPAWN_EGG, $class::NETWORK_ID));
					$player->sendAlert("Başarılı", "Yumurta envanterine eklendi.", "Tamam", "X Kapat");
				}else{
					$player->sendAlert("Hata", "Yeterli paran yok. Dilersen sitemizden oyun parası alabilirsin. Yada mob paketlerini hazır bir şekilde satın alabilirsin.\n»§b stormgames.net", "Tamam", "X Kapat");
				}
			}
		});
		$form->setTitle("Canavar Yumurtaları");
		$form->setContent("§7Buradaki canavar yumurtalarını satın aldıktan sonra ister oluştur, istersen mob spawnera yerleştir. Mob spawner'ı sitemizden satın alabilirsin.\n\nCanavarlar oluşturup onları öldürüp XP ve item kazanabilirsin.");
		foreach(self::$monsters as $class => $data){
			$form->addButton(TextUtils::classStringToName($class) . "\n§8» §2" . Utils::addMonetaryUnit($data['money']) . " §8«", 0, $data['link'] !== '' ? $data['link'] : 'textures/items/spawn_egg');
		}
		$form->addButton("Spawner\n§8» §35 Nakit §8«", 0, 'textures/blocks/mob_spawner');
		$form->sendToPlayer($player);
	}
}