<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\player\Player;
use pocketmine\utils\UUID;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\SGPlayer;

class FloatingText extends Entity{

	public $width = 0.1, $height = 0.1;
	/** @var UUID */
	private $uuid;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setNameTagAlwaysVisible();
		$this->setNameTagVisible();
		$this->setScale(0.01);
		$this->uuid = UUID::fromRandom();
	}

	public function onUpdate(int $currentTick) : bool{
		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0){
			return true;
		}

		$this->lastUpdate = $currentTick;

		if(!$this->isAlive()){
			if($this->onDeathUpdate($tickDiff)){
				$this->flagForDespawn();
			}

			return true;
		}

		//$this->updateMovement();

		$changedProperties = $this->getNetworkProperties()->getDirty();
		if(!empty($changedProperties)){
			$this->sendData($this->hasSpawned, $changedProperties);
			$this->getNetworkProperties()->clearDirtyProperties();
		}

		return true;
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	/**
	 * @param SGPlayer|Player $player
	 */
	protected function sendSpawnPacket(Player $player) : void{
		$name = Language::translateOrExtended($player->getLanguage(), $this->getNameTag());

		$add = new PlayerListPacket();
		$add->type = PlayerListPacket::TYPE_ADD;
		$add->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $name, new Skin("Standard_Custom", str_repeat("\x00", 8192)))];
		$player->getNetworkSession()->sendDataPacket($add);

		$pk = new AddPlayerPacket();
		$pk->uuid = $this->uuid;
		$pk->username =  $name;
		$pk->entityRuntimeId = $this->id;
		$pk->position = $this->location->asVector3();
		$pk->item = ItemFactory::get(ItemIds::AIR, 0, 0);
		$pk->metadata = $this->getNetworkProperties()->getAll();
		$player->getNetworkSession()->sendDataPacket($pk);

		$this->networkProperties->setString(EntityMetadataProperties::NAMETAG, $name);
		$this->sendData($player, $this->networkProperties->getDirty());

		$remove = new PlayerListPacket();
		$remove->type = PlayerListPacket::TYPE_REMOVE;
		$remove->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
		$player->getNetworkSession()->sendDataPacket($remove);
	}

	public function setNameTag(string $name) : void{
		parent::setNameTag($name);
		$this->getNetworkProperties()->clearDirtyProperties();

		/** @var SGPlayer $player */
		foreach($this->hasSpawned as $player){
			$this->networkProperties->setString(EntityMetadataProperties::NAMETAG, Language::translateOrExtended($player->getLanguage(), $name));
			$this->sendData($player, $this->networkProperties->getDirty());
		}
	}
}