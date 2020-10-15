<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Crate;

use pocketmine\block\BlockLegacyIds;
use pocketmine\form\Form;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Pickaxe;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use StormGames\Crate\CrateContents;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\Form\VoteForm;
use StormGames\SGCore\SGPlayer;

class VoteCrateContents extends CrateContents{

	private const CONTENT = [
		17 => [ItemIds::APPLE, 0, 8],
		17 => [ItemIds::BREAD, 0, 8],
		18 => [ItemIds::CARROT, 0, 16],
		17 => [ItemIds::CAKE, 0, 2],
		15 => [ItemIds::CARROT, 0, 16],
		14 => [ItemIds::POTATO, 0, 16],
		16 => [ItemIds::CACTUS, 0, 16],
		18 => [ItemIds::CARROT, 0, 16],
		15 => [ItemIds::STEAK, 0, 16],
		14 => [ItemIds::IRON_PICKAXE],
		13 => [ItemIds::IRON_SWORD],
		12 => [ItemIds::DIAMOND_SWORD],
		11 => [ItemIds::DIAMOND_PICKAXE],
		10 => [ItemIds::DIAMOND, 0, 3],
		9 => [ItemIds::GOLDEN_APPLE, 0, 5],
		3 => [ItemIds::IRON_INGOT, 0, 32],
		3 => [ItemIds::GOLD_INGOT, 0, 32],
		1 => [ItemIds::DIAMOND, 0, 32]
	];

	private static $content = null;

	public function __construct(){
		if(self::$content === null){
			self::$content = [];
			foreach(self::CONTENT as $chance => $item){
				for($i=0; $i<=$chance; $i++){
					self::$content[] = $item;
				}
			}
		}
	}

	public function getName() : string{
		return 'vote';
	}

	public function giveRandomContent(SGPlayer $player, string &$contentName) : void{
		$item = ItemFactory::get(...self::$content[array_rand(self::$content)]);
		if($item instanceof Pickaxe){
			$item->addEnchantment(new EnchantmentInstance(Enchantment::get(Enchantment::EFFICIENCY), $item->getBlockToolHarvestLevel() === ToolTier::DIAMOND() ? 1 : 2));
		}elseif($item instanceof Sword){
			$item->addEnchantment(new EnchantmentInstance(Enchantment::get(Enchantment::SHARPNESS), $item->getBlockToolHarvestLevel() === ToolTier::DIAMOND() ? 1 : 2));
		}
		$player->getInventory()->addItem(EnchantManager::addLoreForCustomEnchantment($player, $item));
		$contentName = "x" . $item->getCount() . " " . $item->getVanillaName();
	}

	public function getPrice() : int{
		return 0;
	}

	public function getBuyForm(SGPlayer $player) : Form{
		return new VoteForm($player);
	}

	public function getBlockId() : int{
		return BlockLegacyIds::CHEST;
	}
}