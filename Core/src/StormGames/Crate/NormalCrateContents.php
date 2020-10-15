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
use StormGames\SGCore\entity\utils\Skins;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;

class NormalCrateContents extends CrateContents{

    public function getName() : string{
        return "normal";
    }

    public function giveRandomContent(SGPlayer $player, string &$contentName) : void{
        if(mt_rand(0, 2) === 1){
            $coins = mt_rand(5, 20);
            $player->addCoins($coins);
            $contentName = "$coins %coins";
        }else{
            $capeName = array_rand(Skins::getCapes());
            $player->addPermissions(DefaultPermissions::ROOT_CAPE . $capeName);
            $contentName = "%capes.$capeName";
        }
    }

    public function getPrice() : int{
        return 50;
    }

	public function getBlockId() : int{
		return BlockLegacyIds::ENDER_CHEST;
	}
}