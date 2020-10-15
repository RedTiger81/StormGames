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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\player\Player;

class Lightning extends Entity{
	public const NETWORK_ID = EntityLegacyIds::LIGHTNING_BOLT;

	public $width = 0.3;
	public $height = 1.8;

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(2);
		parent::initEntity($nbt);
	}

	public function entityBaseTick(int $tickDiff = 1): bool{
		parent::entityBaseTick($tickDiff);

		if($this->ticksLived > 20){
			$this->flagForDespawn();
		}

		return true;
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$explode = new ExplodePacket();
		$explode->position = $this;
		$explode->radius = 10;
		$player->getNetworkSession()->sendDataPacket($explode);
	}
}