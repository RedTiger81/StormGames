<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\tiles\Hopper;

class CobblestoneReward{
    public const KEYS = [
        3 => [ItemIds::GHAST_TEAR, "§2Antik Kasa"],
        2 => [ItemIds::NAME_TAG, "§ePara Kasası"],
        1 => [ItemIds::BLAZE_POWDER, "§dEfsanevi Kasa"]
    ];

    public const MINE = [
        5 => [ItemIds::COAL, ""],
        4 => [ItemIds::EMERALD, ""],
        3 => [ItemIds::GOLD_INGOT, ""],
        2 => [ItemIds::IRON_INGOT, ""],
        1 => [ItemIds::DIAMOND, ""]
    ];

    public static function run(BlockBreakEvent $event){
    	/** @var SkyPlayer $player */
        $player = $event->getPlayer();
        $rand = mt_rand(0, 99);
        $item = $event->getBlock()->asItem();
	    $keys = [self::KEYS, self::MINE][rand(0, 1)];
        ksort($keys);

        foreach($keys as $key => $data){
	        if($player->hasPermission(DefaultPermissions::VIP_PLUS)){
		        $key += 3;
	        }elseif($player->hasPermission(DefaultPermissions::VIP_PLUS)){
                $key += 2;
            }elseif($player->hasPermission(DefaultPermissions::VIP)){
                $key += 1;
            }
            if($rand <= $key){
                $item = ItemFactory::get($data[0])->setCustomName($data[1]);
                $player->sendPopup("§f» {$item->getName()} §7çıktı §f«");
                $player->getWorld()->broadcastLevelEvent($player->getEyePos(), LevelSoundEventPacket::SOUND_NOTE);
                break;
            }
        }
        if(mt_rand(0, 25) < 5){
            $player->getXpManager()->addXp(1);
        }

         if($event->getBlock()->getSide(0)->getId() === BlockLegacyIds::HOPPER_BLOCK){
            /** @var Hopper $tile*/
            $tile = $player->getWorld()->getTile($event->getBlock()->getSide(0)->getPos());
            if($tile instanceof Hopper){
                $inventory = $tile->getInventory();
                if($inventory->canAddItem($item)){
                    $inventory->addItem($item);
                    $event->setDrops([]);
                    return;
                }
            }

        }
        $event->setDrops([$item]);
    }

    public static function getDrop(Item $item): Item{
	    $keys = [self::KEYS, self::MINE][rand(0, 1)];
	    $rand = mt_rand(0, 99);
	    foreach($keys as $key => $data){
		    if($rand <= $key){
		    	return ItemFactory::get($data[0])->setCustomName($data[1]);
		    }
	    }
	    return $item;
    }
}