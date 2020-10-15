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
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\world\Position;

class Woodcutter extends MiniMe{
	public const PLANT_TIME = 40;
	public $lastPlant = 0;


	public function breakProgress(int $currentTick){
		if($this->lastPlant === 500 && $currentTick % 49 === 0) $this->lastPlant = 0;

		if($this->isPlace($this->block)){
			if($this->lastPlant >= self::PLANT_TIME){
				$this->plantTree();
			}else{
				$this->lastPlant++;
			}
			return;
		}
		if($this->breakTime <= $this->currentBreak){
			$this->stopBreakBlock();
		}else{
			$this->attackBlock();
			$this->continueBreakBlock();
		}
	}

	public function plantTree(): void{
		$item = ItemFactory::get(ItemIds::SAPLING);
		$this->broadcastEntityEvent(ActorEventPacket::ARM_SWING, null, $this->getViewers());
		$this->getWorld()->useItemOn($this->block->getPos(), $item, 1, new Vector3(0.7375,1,0.7455),  null, true);
		$this->stopBreakBlock();
		$this->lastPlant = 500;
	}

	public function onSuccess() : void{
		if(!$this->isPlace($this->block, true)){
			$this->getInventory()->addItem(...$this->block->getDropsForCompatibleTool($this->getHandItem()));
			$this->getWorld()->setBlock($this->block->getPos(), BlockFactory::get(0));
		}
	}

	public function getBlockIds() : array{
		return [BlockLegacyIds::LEAVES, BlockLegacyIds::LEAVES2, BlockLegacyIds::LOG, BlockLegacyIds::LOG2];
	}

	public function isPlace(Block $block, bool $force = false): bool{
		return in_array($block->getId(), [BlockLegacyIds::FARMLAND, BlockLegacyIds::GRASS, BlockLegacyIds::DIRT]) && ($force || $block->getSide(1)->getId() === 0);
	}

	public function controlBlock(Block $block) : bool{
		return parent::controlBlock($block) || ($this->isPlace($block) && $this->lastPlant <= self::PLANT_TIME && $block->getPos()->distance($this->getPosition()) < 2);
	}

	public function getHandItem() : Item{
		if($this->item === null){
			$this->item = [
				ItemFactory::get(ItemIds::STONE_AXE),
				ItemFactory::get(ItemIds::IRON_AXE),
				ItemFactory::get(ItemIds::DIAMOND_AXE)
			][$this->level-1];
		}
		return $this->item;
	}

	public function lookAtInto(Position $target) : void{
		$xDist = $target->x - $this->location->x;
		$zDist = $target->z - $this->location->z;

		$horizontal = sqrt($xDist ** 2 + $zDist ** 2);
		$vertical = ($target->y - $this->location->y) + 0.55;
		$this->location->pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down
		$this->location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($this->location->yaw < 0){
			$this->location->yaw += 360.0;
		}
		$this->updateMovementInto($target->getWorld());
	}
}