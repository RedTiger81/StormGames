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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Crops;
use pocketmine\block\Stem;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\ActorEventPacket;

class Farmer extends MiniMe{
	private const BLOCK_AND_SEEDS = [
		BlockLegacyIds::WHEAT_BLOCK => ItemIds::WHEAT_SEEDS,
		BlockLegacyIds::BEETROOT_BLOCK => ItemIds::BEETROOT_SEEDS,
		BlockLegacyIds::MELON_BLOCK => ItemIds::AIR,
		BlockLegacyIds::PUMPKIN => ItemIds::AIR,
		BlockLegacyIds::POTATO_BLOCK => ItemIds::POTATO,
		BlockLegacyIds::CARROT_BLOCK => ItemIds::CARROT
	];

	public function onSuccess() : void{
		$this->getInventory()->addItem(...$this->block->getDropsForCompatibleTool($this->getHandItem()));
		$item = ItemFactory::get(self::BLOCK_AND_SEEDS[$this->block->getId()]);
		$this->getWorld()->setBlock($this->block->getPos(), BlockFactory::get(0));

		if($item->getId() !== 0){
			$this->broadcastEntityEvent(ActorEventPacket::ARM_SWING, null, $this->getViewers());
			$this->getWorld()->useItemOn($this->block->getPos()->subtract(0, 1), $item, 1, null, null, true);
		}
	}

	public function getBlockIds() : array{
		return array_keys(self::BLOCK_AND_SEEDS);
	}

	public function getHandItem() : Item{
		if($this->item === null){
			$this->item = [
				ItemFactory::get(ItemIds::STONE_HOE),
				ItemFactory::get(ItemIds::IRON_HOE),
				ItemFactory::get(ItemIds::DIAMOND_HOE)
			][$this->level-1];
		}
		return $this->item;
	}

	public function controlBlock(Block $block) : bool{
		if($block instanceof Stem) return false;
		return (parent::controlBlock($block) && !$block instanceof Crops) ? true : ($block instanceof Crops && $block->getMeta() >= 7) ;
	}

	public function breakProgress(int $currentTick){
		if($currentTick % $this->getSpeed() === 0){
			$this->attackBlock();
			$this->continueBreakBlock();
			$this->stopBreakBlock();
		}
	}

	public function getSpeed(): int{
		return [60, 40, 20][$this->level-1];
	}

	public function getArea(): int{
		return [3, 4, 5][$this->level-1];
	}
}