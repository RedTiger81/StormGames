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
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\player\Player;
use StormGames\SGCore\SGPlayer;

class CommandHuman extends RDHuman{
	/** @var string */
	private $command;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setNameTagAlwaysVisible();
		$this->setNameTagVisible();
		$this->setImmobile(false);
		$this->command = $nbt->getString("command", "");
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() !== EntityDamageEvent::CAUSE_VOID){
			if($source instanceof EntityDamageByEntityEvent and $source->getDamager() instanceof Player){
				/** @noinspection PhpParamsInspection */ // blame phpStorm
				$this->command($source->getDamager());
			}
			return;
		}

		parent::attack($source);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		$this->command($player);
		return true;
	}

	public function command(Player $player) : void{
		$this->server->dispatchCommand($player, $this->command, true);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setString("command", $this->command);
		return $nbt;
	}

	/**
	 * @param SGPlayer|Player $player
	 *
	 * @throws \ReflectionException
	 */
	protected function sendSpawnPacket(Player $player) : void{
		if(!$this->skin){
			throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
		}

		$name = $this->getNameTag();

		$player->getNetworkSession()->sendDataPacket(PlayerListPacket::add([PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $name, $this->skin)]));

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

		$this->networkProperties->setString(EntityMetadataProperties::NAMETAG, $name);
		$this->sendData($player, $this->networkProperties->getDirty());

		$player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]));
	}


	public function canBeCollidedWith(): bool{
		return false;
	}

	public function canCollideWith(Entity $entity): bool{
		return false;
	}

	public function lookAtInto(Player $target): void{
		$xDist = $target->getLocation()->x - $this->location->x;
		$zDist = $target->getLocation()->z - $this->location->z;

		$horizontal = sqrt($xDist ** 2 + $zDist ** 2);
		$vertical = ($target->getLocation()->y - $this->location->y) + 0.55;
		$this->location->pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down
		$this->location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($this->location->yaw < 0) {
			$this->location->yaw += 360.0;
		}
		$this->updateMovementInto($target);
	}

	private function updateMovementInto(Player $player){
		$pk = new MoveActorAbsolutePacket();
		$pk->entityRuntimeId = $this->id;
		$pk->position = $this->getOffsetPosition($this->location);
		$pk->xRot = $this->location->pitch;
		$pk->yRot = $this->location->yaw;
		$pk->zRot = $this->location->yaw;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public function updateMovement(bool $teleport = false): void{}
}