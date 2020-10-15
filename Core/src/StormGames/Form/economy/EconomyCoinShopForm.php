<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\economy;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\enchant\CustomEnchantment;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\EconomyForm;
use StormGames\Prefix;

class EconomyCoinShopForm{
	/** @var Item[] */
	public static $items = [];

	public function __construct(SGPlayer $player){
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName(Prefix::ECONOMY());
		$menu->readonly(true);
		$inventory = $menu->getInventory();

		for($i=0;$i<$inventory->getSize();$i++){
			$inventory->setItem($i, ItemFactory::get(ItemIds::GLASS_PANE, DyeColor::BLACK()->getMagicNumber()));
		}
		$inventory->setItem(22, ItemFactory::get(ItemIds::WOOL, DyeColor::YELLOW()->getMagicNumber())->setCustomName($player->translate("kit.back")));
		$inventory->setItem(21, ItemFactory::get(ItemIds::WOOL, DyeColor::RED()->getMagicNumber())->setCustomName($player->translate("kit.close")));

		/**
		 * @var int $index
		 * @var Item $item
		 */
		foreach(self::getItems() as $index => $item){
			$item = clone $item;
			$inventory->setItem($index, EnchantManager::addLoreForCustomEnchantment($player, $item));
		}
		$menu->setListener(function(Player $player, Item $in, Item $out, SlotChangeAction $action) use($menu){
			if($in->getId() === ItemIds::GLASS_PANE) return;
			$menu->onClose($player);
			/** @var SGPlayer $player */
			if($in->getId() === ItemIds::WOOL){
				if($in->getMeta() === DyeColor::YELLOW()->getMagicNumber()){
					$player->sendForm(new EconomyForm($player));
				}
			}elseif($player->getCoins() >= ($price = (int) preg_replace('/[^ .%0-9]/', '', TextFormat::clean($in->getName())))){
				if($player->getInventory()->canAddItem($in)){
					$player->reduceCoins($price);
					$player->getInventory()->addItem(EnchantManager::addLoreForCustomEnchantment($player, $in));
					$player->sendMessage(Prefix::ECONOMY() . TextFormat::GREEN . $player->translate('forms.economy.coinShop.buy.success', [$in->getCustomName() . TextFormat::RESET]));
				}else{
					$player->sendMessage(Prefix::ECONOMY() . TextFormat::RED . $player->translate('error.generic.fullInventory'));
				}
			}else{
				$player->sendMessage(Prefix::ECONOMY() . TextFormat::RED . $player->translate('error.generic.noCoins'));
			}
		});
		self::globalCloseEvent($menu);
		$menu->send($player);
	}

	public static function getItems() : array{
		if(empty(self::$items)){
			$sword = ItemFactory::get(ItemIds::DIAMOND_SWORD);
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::SHARPNESS(), 5));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::KNOCKBACK(), 2));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::FIRE_ASPECT(), 2));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::LIFESTEAL), 5));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::DEATHBRINGER), 1));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::VAMPIRE), 1));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::BLIND), 3));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::ICE_ASPECT), 3));
			$sword->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::POISON), 1));
			$sword->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'ZULFIQAR §7- §b' . 20 . ' Nakit');

			$pickaxe = ItemFactory::get(ItemIds::DIAMOND_PICKAXE);
			$pickaxe->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 10));
			$pickaxe->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::FURNACE), 1));
			$pickaxe->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::ENERGIZING), 4));
			$pickaxe_2 = clone $pickaxe;
			$pickaxe->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'DRAGON §7- §b' . 15 . ' Nakit');

			$pickaxe_2->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::DRAGON_PLUS)));
			$pickaxe_2->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'DRAGON PLUS §7- §b' . 50 . ' Nakit');

			$shovel = ItemFactory::get(ItemIds::DIAMOND_SHOVEL);
			$shovel->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 10));
			$shovel->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::ENERGIZING), 4));
			$shovel->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'FACING §7- §b' . 7 . ' Nakit');

			$axe = ItemFactory::get(ItemIds::DIAMOND_AXE);
			$axe->addEnchantment(new EnchantmentInstance(Enchantment::EFFICIENCY(), 10));
			$axe->addEnchantment(new EnchantmentInstance(Enchantment::SHARPNESS(), 5));
			$axe->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::ENERGIZING), 4));
			$axe->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'AXECALIBUR §7- §b' . 10 . ' Nakit');

			$helmet = ItemFactory::get(ItemIds::DIAMOND_HELMET);
			$helmet->addEnchantment(new EnchantmentInstance(Enchantment::RESPIRATION(), 3));
			$helmet->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'DIAMONDHEAD §7- §b' . 10 . ' Nakit');

			$boots = ItemFactory::get(ItemIds::DIAMOND_BOOTS);
			$boots->addEnchantment(new EnchantmentInstance(Enchantment::FEATHER_FALLING(), 4));
			$boots->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'BIGFOOT §7- §b' . 10 . ' Nakit');

			self::$items = array_map(function(Item $item){
				if($item instanceof Armor){
					$item = $item->addEnchantment(new EnchantmentInstance(Enchantment::PROTECTION(), 4))->addEnchantment(new EnchantmentInstance(Enchantment::FIRE_PROTECTION(), 4))->addEnchantment(new EnchantmentInstance(Enchantment::BLAST_PROTECTION(), 4))->addEnchantment(new EnchantmentInstance(Enchantment::PROJECTILE_PROTECTION(), 4))->addEnchantment(new EnchantmentInstance(Enchantment::THORNS(), 3));
				}
				return $item->addEnchantment(new EnchantmentInstance(Enchantment::UNBREAKING(), 3))->addEnchantment(new EnchantmentInstance(Enchantment::MENDING(), 1));
			}, [$sword, $pickaxe, $axe, $helmet, ItemFactory::get(ItemIds::DIAMOND_CHESTPLATE)->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'STORMPLATE §7- §b' . 15 . ' Nakit'), ItemFactory::get(ItemIds::DIAMOND_LEGGINGS)->setCustomName(TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . 'ELEPHANT LEGS §7- §b' . 10 . ' Nakit'), $boots, $pickaxe_2, $shovel]);
		}

		return self::$items;
	}

	public static function globalCloseEvent(InvMenu &$menu): void{
		$menu->setInventoryCloseListener(function(SGPlayer $player, InvMenuInventory $inventory){
			$player->sendForm(new EconomyForm($player));
		});
	}
}