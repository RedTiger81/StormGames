<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\player\Player;

trait ItemHolderTrait {
	/** @var Item|null $mainHand */
	protected $mainHand;
	/** @var Item|null $offHand */
	protected $offHand;
	/** @var bool $dropAll */
	protected $dropAll = true;

	public function initEntity(CompoundTag $nbt) : void {
		if($nbt->hasTag("Mainhand", ListTag::class)) {
			$this->mainHand = Item::nbtDeserialize($nbt->getListTag("Mainhand")->first());
		}
		if($nbt->hasTag("Offhand", ListTag::class)) {
			$this->offHand = Item::nbtDeserialize($nbt->getListTag("Offhand")->first());
		}
		if($nbt->hasTag("Armor", ListTag::class)) {
			foreach($nbt->getListTag("Armor")->getValue() as $tag)
				$items[] = Item::nbtDeserialize($tag);
			$this->getArmorInventory()->setContents($items);
		}
		parent::initEntity($nbt);
	}

	/**
	 * @return bool
	 */
	public function isDropAll() : bool {
		return $this->dropAll;
	}

	/**
	 * @param bool $dropAll
	 *
	 * @return ItemHolderTrait
	 */
	public function setDropAll(bool $dropAll = true) {
		$this->dropAll = $dropAll;
		return $this;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array {
		$drops = parent::getDrops();
		if($this->dropAll) {
			$drops[] = $this->mainHand ?? ItemFactory::get(ItemIds::AIR);
			$drops[] = $this->offHand ?? ItemFactory::get(ItemIds::AIR);
		}elseif(mt_rand(1, 1000) <= 85) {
			$drops[] = $this->mainHand ?? ItemFactory::get(ItemIds::AIR);
			// TODO: Should the offhand drop here too?
		}
		return $drops;
	}

	/**
	 * @return null|Item
	 */
	public function getMainHand() : ?Item {
		return $this->mainHand;
	}

	/**
	 * @param Item $mainHand
	 *
	 * @return ItemHolderTrait
	 */
	public function setMainHandItem(?Item $mainHand){
		$this->mainHand = $mainHand;
		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->item = $this->mainHand ?? ItemFactory::get(ItemIds::AIR);
		$pk->inventorySlot = $pk->hotbarSlot = ContainerIds::INVENTORY;
		/** @var Player $player */
		foreach($this->getViewers() as $player)
			$player->getNetworkSession()->sendDataPacket($pk);
		return $this;
	}

	/**
	 * @return null|Item
	 */
	public function getOffHand() : ?Item {
		return $this->offHand;
	}

	/**
	 * @param Item $offHand
	 *
	 * @return ItemHolderTrait
	 */
	public function setOffHandItem(?Item $offHand) {
		$this->offHand = $offHand;
		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->item = $this->offHand ?? ItemFactory::get(ItemIds::AIR);
		$pk->inventorySlot = $pk->hotbarSlot = ContainerIds::OFFHAND;
		foreach($this->getViewers() as $player)
			$player->getNetworkSession()->sendDataPacket($pk);
		return $this;
	}

	public function saveNBT() : CompoundTag {
		$nbt = parent::saveNBT();
		if(isset($this->mainHand)) {
			$nbt->setTag("Mainhand", new ListTag([$this->mainHand->nbtSerialize()], NBT::TAG_Compound));
		}
		if(isset($this->offHand)) {
			$nbt->setTag("Offhand", new ListTag([$this->offHand->nbtSerialize()], NBT::TAG_Compound));
		}
		return $nbt;
	}

	protected function sendSpawnPacket(Player $player) : void {
		parent::sendSpawnPacket($player);
		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->item = $this->mainHand ?? ItemFactory::get(ItemIds::AIR);
		$pk->inventorySlot = $pk->hotbarSlot = ContainerIds::INVENTORY;
		$player->getNetworkSession()->sendDataPacket($pk);
		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->item = $this->offHand ?? ItemFactory::get(ItemIds::AIR);
		$pk->inventorySlot = $pk->hotbarSlot = ContainerIds::OFFHAND;
		$player->getNetworkSession()->sendDataPacket($pk);
	}
}