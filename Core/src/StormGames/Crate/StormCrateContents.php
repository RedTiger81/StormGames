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
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use StormGames\Crate\CrateContents;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\EnchantmentUtils;

class StormCrateContents extends CrateContents{

    private const CONTENTS = [
        [ItemIds::DIAMOND_SWORD],
        [ItemIds::DIAMOND_AXE],
        [ItemIds::DIAMOND_PICKAXE],
        [ItemIds::DIAMOND_SHOVEL],
        [ItemIds::DIAMOND_HELMET],
        [ItemIds::DIAMOND_CHESTPLATE],
        [ItemIds::DIAMOND_LEGGINGS],
        [ItemIds::DIAMOND_BOOTS],
        [ItemIds::GOLDEN_PICKAXE],
	    [ItemIds::IRON_BOOTS],
	    [ItemIds::IRON_AXE],
	    [ItemIds::IRON_CHESTPLATE],
	    [ItemIds::IRON_LEGGINGS],
	    [ItemIds::IRON_HELMET],
	    [ItemIds::IRON_SHOVEL],
	    [ItemIds::IRON_SWORD],
    ];

    public function getName() : string{
        return "storm";
    }

    public function giveRandomContent(SGPlayer $player, string &$contentName) : void{
        $item = ItemFactory::get(...self::CONTENTS[array_rand(self::CONTENTS)]);
        $ench_ = EnchantmentUtils::availableEnchantments($item);
        $ench = $ench_[array_rand($ench_)];
	    $item->addEnchantment(new EnchantmentInstance($ench, mt_rand(1, $ench->getMaxLevel())));
	    if(mt_rand(0, 10) === 10){
		    $item->addEnchantment(new EnchantmentInstance($ench_[array_rand($ench_)], mt_rand(1, $ench->getMaxLevel())));
	    }
        $player->getInventory()->addItem(EnchantManager::addLoreForCustomEnchantment($player, $item));
        $contentName = $item->getVanillaName();
    }

    public function getPrice() : int{
        return 50000;
    }

    public function getBuyForm(SGPlayer $player) : Form{
        return new BuyCrateKeyForm($player, $this->getName(), $this->getPrice());
    }

	public function getBlockId() : int{
		return BlockLegacyIds::MAGMA;
	}
}