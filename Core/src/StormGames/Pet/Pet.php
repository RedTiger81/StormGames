<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Pet;

use pocketmine\entity\Animal;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\form\FormIcon;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;
use StormGames\Form\PetCustomizeForm;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\TextUtils;

abstract class Pet extends Animal{
	protected const FOLLOW_RANGE_SQ = 1.2;

	/** @var SGPlayer */
	protected $petOwner;
	/** @var int */
	protected $jumpTicks = 0;
	/** @var float|int */
	protected $customGravity;

	public function __construct(World $level, CompoundTag $nbt, ?SGPlayer $owner = null){
		$this->petOwner = $owner;
		parent::__construct($level, $nbt);
		$this->jumpVelocity = $this->gravity * 15;
		$this->customGravity = -$this->gravity * 4;
		$this->setOwningEntity($owner);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		if($this->petOwner === null){
			$this->flagForDespawn();
			return;
		}

		$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::TAMED, true);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
		$this->setCanSaveWithChunk(false);
		$this->setImmobile();
		$this->setScoreTag(TextFormat::GRAY . $this->petOwner->getName());

	}

	final public static function getPetName() : string{
		return "pets." . strtolower(TextUtils::classStringToName(static::class));
	}

	protected function getSpeed() : float{
		return 1.0;
	}

	public function follow(Entity $target) : void{
		$x = $target->getPosition()->x - $this->location->x;
		$y = $target->getPosition()->y - $this->location->y;
		$z = $target->getPosition()->z - $this->location->z;
		$xz_modulus = sqrt($xz_sq = $x * $x + $z * $z);
		if($xz_sq < self::FOLLOW_RANGE_SQ){
			$this->motion->x = $this->motion->z = 0;
		}else{
			$speed_factor = $this->getSpeed() * 0.15;
			$this->motion->x = $speed_factor * ($x / $xz_modulus);
			$this->motion->z = $speed_factor * ($z / $xz_modulus);
		}
		$this->location->yaw = rad2deg(atan2(-$x, $z));
		$this->location->pitch = rad2deg(-atan2($y, $xz_modulus));
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
	}

	/**
	 * @param SGPlayer $player
	 *
	 * @return bool
	 * @see \StormGames\Form\menu\cosmetic\CosmeticPetsForm Line 37
	 */
	public static function canUse(SGPlayer $player) : bool{
		return $player->hasPermission(DefaultPermissions::MVP);
	}

	public static function getFormIcon() : FormIcon{
		return new FormIcon('http://minestormpe.com/image/pet/' . strtolower(TextUtils::classStringToName(static::class)) . '.png');
	}

	final public function getName() : string{
		return self::getPetName();
	}

	private function checkUpdateRequirements() : bool{
		if($this->petOwner === null || ($this->petOwner !== null && $this->petOwner->isClosed())){
			$this->flagForDespawn();
			return false;
		}

		return $this->petOwner->isAlive();
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->closed){
			return false;
		}

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

		if(!$this->checkUpdateRequirements()){
			return true;
		}

		$this->entityBaseTick($tickDiff);

		// Sahibine uzaksa ışınlan
		if($this->location->distanceSquared($this->petOwner->location) >= 1600){
			$this->teleport($this->petOwner->location);
			return true;
		}

		if($this->jumpTicks > 0){
			--$this->jumpTicks;
		}

		if(!$this->onGround){
			if($this->motion->y > $this->customGravity){
				$this->motion->y = $this->customGravity;
			}else{
				$this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
			}
		}else{
			if($this->isCollidedHorizontally && $this->jumpTicks === 0){
				$this->jump();
			}else{
				$this->motion->y -= $this->gravity;
			}
		}
		$this->follow($this->petOwner);

		$this->updateMovement();
		$this->motion->setComponents(0, 0, 0);

		return true;
	}

	public function jump() : void{
		parent::jump();
		$this->jumpTicks = 5;
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			$this->teleport($this->petOwner->location);
			return;
		}
		if($source instanceof EntityDamageByEntityEvent){
			/** @var SGPlayer $player */
			$player = $source->getDamager();
			if($this->petOwner instanceof SGPlayer && $this->petOwner->getId() === $player->getId()){
				$player->sendForm(new PetCustomizeForm($player, $this));
			}
		}
	}
}