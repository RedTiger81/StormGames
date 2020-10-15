<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataTypes;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\player\Player;
use raklib\utils\InternetAddress;
use StormGames\Prefix;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\SGPlayer;

class TransferHuman extends RDHuman{

	/** @var InternetAddress */
	private $transfer;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$transfer = explode(":", $nbt->getString("Transfer", "play.rosedust.net:19132"));
		$this->transfer = new InternetAddress($transfer[0], (int) $transfer[1], 4);
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() !== EntityDamageEvent::CAUSE_VOID){
			if($source instanceof EntityDamageByEntityEvent and $source->getDamager() instanceof Player){
				/** @noinspection PhpParamsInspection */ // blame phpStorm
				$this->transfer($source->getDamager());
			}
			return;
		}

		parent::attack($source);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		$this->transfer($player);
		return true;
	}

	public function transfer(Player $player) : void{
		$pk = new TransferPacket();
		$pk->address = $this->transfer->getIp();
		$pk->port = $this->transfer->getPort();
		$player->getNetworkSession()->sendDataPacket($pk, true);
		$player->disconnect('', 'Transfer to ' . $this->transfer->getIp() . ':' . $this->transfer->getPort(), false);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setString("Transfer", $this->transfer->getIp() . ":" . $this->transfer->getPort());
		return $nbt;
	}

	/**
	 * @param SGPlayer|Player $player
	 * @throws \ReflectionException
	 */
	protected function sendSpawnPacket(Player $player) : void{
		if(!$this->skin){
			throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
		}

		$name = sprintf(Prefix::FORM_TITLE, Language::translateExtended($player->getLanguage(), $this->getNameTag()));

		if(!($this instanceof Player)){
			/* we don't use Server->updatePlayerListData() because that uses batches, which could cause race conditions in async compression mode */
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_ADD;
			$pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $name, $this->skin)];
			$player->getNetworkSession()->sendDataPacket($pk);
		}

		$pk = new AddPlayerPacket();
		$pk->uuid = $this->getUniqueId();
		$pk->username = $name;
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->location->asVector3();
		$pk->motion = $this->getMotion();
		$pk->yaw = $this->location->yaw;
		$pk->pitch = $this->location->pitch;
		$pk->item = $this->getInventory()->getItemInHand();
		$pk->metadata = $this->getNetworkProperties()->getAll();
		$player->getNetworkSession()->sendDataPacket($pk);

		//TODO: Hack for MCPE 1.2.13: DATA_NAMETAG is useless in AddPlayerPacket, so it has to be sent separately
		$this->sendData($player, [EntityMetadataProperties::NAMETAG => [EntityMetadataTypes::STRING, $name]]);

		if(!($this instanceof Player)){
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_REMOVE;
			$pk->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}
}