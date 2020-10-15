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

namespace Eren5960\Mini_me\entity;

use Eren5960\SkyBlock\CobblestoneReward;
use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Miner extends MiniMe{
	public function onSuccess() : void{
		$this->getInventory()->addItem(CobblestoneReward::getDrop($this->block->asItem()));
		$this->getWorld()->setBlock($this->block->getPos(), BlockFactory::get(0));
	}

	public function getBlockIds() : array{
		$player = $this->getOwner();
		if($player instanceof SkyPlayer && $player->isInIsland() && $player->getNowIsland()->isOwner($player)){
			return [$player->getNowIsland()->getCobblestoneId()];
		}
		return [4];
	}

	public function getHandItem() : Item{
		if($this->item === null){
			$this->item = [
				ItemFactory::get(ItemIds::STONE_PICKAXE),
				ItemFactory::get(ItemIds::IRON_PICKAXE),
				ItemFactory::get(ItemIds::DIAMOND_PICKAXE)
			][$this->level-1];
		}
		return $this->item;
	}
}