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

namespace StormGames\SGCore\tiles;

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class Hopper extends \pocketmine\block\tile\Hopper{
	/** @var int */
	public $transferCooldown = 8;
	/** @var AxisAlignedBB */
	public $pullBox;

	public function __construct(World $level, Vector3 $pos){
		parent::__construct($level, $pos);
		$this->pullBox = new AxisAlignedBB($pos->x, $pos->y, $pos->z, $pos->x + 1, $pos->y + 1.5, $pos->z + 1);
	}

	/*public function readSaveData(CompoundTag $nbt) : void{
		parent::readSaveData($nbt);
		$this->inventory = new HopperInventory($this->getPos());
	}*/


	public function onUpdate() : bool{
		if($this->closed){
			return false;
		}

		if($this->isOnTransferCooldown()){
			$this->transferCooldown--;
		}else{
			$transfer = false;

			if(!$this->isEmpty()){
				$transfer = $this->transferItemOut();
			}

			if(!$this->isFull()){
				$transfer = $this->pullItemFromTop() || $transfer;
			}

			if($transfer){
				$this->setTransferCooldown(8);
			}
		}

		return true;
	}

	public function isEmpty() : bool{
		return count($this->getInventory()->getContents()) === 0;
	}

	public function isFull() : bool{
		foreach($this->getInventory()->getContents(true) as $slot => $item){
			if($item->getMaxStackSize() !== $item->getCount()){
				return false;
			}
		}
		return true;
	}

	public function transferItemOut() : bool{
		$tile = $this->getPos()->getWorld()->getTile($this->getPos()->getSide($this->getBlock()->getMeta()));

		if($tile instanceof InventoryHolder){
			$targetInventory = $tile->getInventory();

			foreach($this->getInventory()->getContents() as $slot => $item){
				$item->setCount(1);

				if($targetInventory->canAddItem($item)){
					$targetInventory->addItem($item);
					$this->getInventory()->removeItem($item);

					if($tile instanceof Hopper){
						$tile->setTransferCooldown(8);
					}

					return true;
				}
			}
		}

		return false;
	}

	public function pullItemFromTop() : bool{
		$tile = $this->getPos()->getWorld()->getTile($this->getPos()->up());

		if($tile instanceof InventoryHolder){
			$inv = $tile->getInventory();
			foreach($inv->getContents() as $slot => $item){

				if($inv instanceof FurnaceInventory){
					//So only results of Furnaces go trough
					if($slot !== 2) continue;
				}

				$item->setCount(1);

				if($this->getInventory()->canAddItem($item)){
					$this->getInventory()->addItem($item);
					$inv->removeItem($item);

					return true;
				}
			}
		}else{
			/** @var ItemEntity $entity */
			foreach(array_filter($this->getPos()->getWorld()->getNearbyEntities($this->pullBox), function(Entity $entity) : bool{
				return $entity instanceof ItemEntity and !$entity->isFlaggedForDespawn();
			}) as $entity){
				$item = $entity->getItem();
				if($this->getInventory()->canAddItem($item)){
					$this->getInventory()->addItem($item);

					$entity->flagForDespawn();

					return true;
				}
			}
		}

		return false;
	}

	public function isOnTransferCooldown() : bool{
		return $this->transferCooldown > 0;
	}

	public function setTransferCooldown(int $cooldown){
		$this->transferCooldown = $cooldown;
	}
}