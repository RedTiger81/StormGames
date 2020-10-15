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
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Crate\CrateContents;
use StormGames\Form\CrateForm;
use StormGames\GenericSound;
use StormGames\SGCore\item\Firework;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class Crate extends Entity{
	public const NETWORK_ID = EntityLegacyIds::MINECART;

	public const PI_DIV_10 = M_PI / 10;

	public $width = 1, $height = 0.4;

	/** @var FloatingText */
	public $floatingText;
	/** @var CrateContents */
	protected $content;

	/** @var SGPlayer|null */
	protected $openedPlayer = null;

	/** @var float */
	public $particleYaw = 0;
	/** @var int */
	protected $particleTick = 0;
	/** @var Vector3 */
	protected $particlePos;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->floatingText = $this->findFloatingText();
		if($this->floatingText == null){
			$this->floatingText = $this->getWorld()->getEntity(Utils::addFloatingText(Position::fromObject($this->location->subtract(0, 1), $this->location->getWorld()), ""));
		}
		$this->content = CrateContents::getCrateContent($nbt->getString("CrateTier", "vote"));
		assert($this->content !== null);
		$this->floatingText->setNameTag(TextFormat::GREEN . " %crate.title " . TextFormat::EOL . TextFormat::WHITE . " %crate.tier." . $this->getCrateTier() . " ");

		$this->particlePos = new Vector3();
		$this->setInvisible();
		$this->setScale(1.25);
		$this->setDisplayBlock($this->content->getBlockId());
		$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		/** @var SGPlayer $player */
		$player->sendForm(new CrateForm($player, $this));

		return true;
	}

	public function attack(EntityDamageEvent $source) : void{
		/** @var SGPlayer $player */
		if($source instanceof EntityDamageByEntityEvent and ($player = $source->getDamager()) instanceof SGPlayer){
			$player->sendForm(new CrateForm($player, $this));
		}else{
			parent::attack($source);
		}
	}

	public function openCrate(SGPlayer $player) : bool{
		if(!$this->isOpening()){
			$this->motion->y = -0.09;
			$this->openedPlayer = $player;
			$this->lookAt($this->openedPlayer->getPosition());
			$crateContent = "";
			$this->content->giveRandomContent($this->openedPlayer, $crateContent);
			$this->floatingText->setNameTag(TextFormat::GOLD . " %crate.congratulations " . TextFormat::EOL . TextFormat::YELLOW . " " . $crateContent . " ");
			$this->setOnFire(3);
			return true;
		}

		return false;
	}

	public function closeCrate() : void{
		$this->openedPlayer = null;
		$this->location->yaw = 0;
		$this->floatingText->setNameTag(TextFormat::GREEN . " %crate.title " . TextFormat::EOL . TextFormat::WHITE . " %crate.tier." . $this->getCrateTier());
		$this->setOnFire(0);
	}

	public function onUpdate(int $currentTick) : bool{
		if(empty($this->getViewers())) return false;
		// Animation
		if(!$this->isOpening()){
			$this->location->yaw += 2;
			$this->motion->y = $this->location->yaw > 180 ? -0.01 : 0.01;
			if($this->location->yaw >= 360){
				$this->location->yaw = 0;
			}

		}else{
			if($this->onGround){
				$this->particleYaw += self::PI_DIV_10;
				$this->particlePos->setComponents(-sin($this->particleYaw) + $this->location->x, $this->location->y, cos($this->particleYaw) + $this->location->z);
				$fark = $currentTick - $this->particleTick;
				if($fark <= 15){
					/** @var Firework $fireworks */
                    $fireworks = new Firework();
					$fireworks->addExplosion(0, chr(mt_rand(0, 15)));
					$nbt = EntityFactory::createBaseNBT($this->particlePos, null, lcg_value() * 360, 90);
					/** @var FireworkRocket $entity */
					$entity = EntityFactory::create(FireworkRocket::class, $this->location->getWorld(), $nbt, $fireworks);
					$entity->setLifeTime(13);
					$entity->spawnToAll();
					$this->location->getWorld()->addSound($this->location, new GenericSound(LevelSoundEventPacket::SOUND_TWINKLE));
				}elseif($fark > 70){
					$this->closeCrate();
					return true;
				}
				$this->location->getWorld()->addParticle($this->particlePos->setComponents($this->particlePos->x, $this->particlePos->y + 1, $this->particlePos->z), new FlameParticle(), $this->hasSpawned);
			}else{
				$this->particleTick = $this->lastUpdate;
			}
		}

		if($currentTick % 20 == 0){
			$this->floatingText->setPosition($this->location->add(0, 1, 0));
			$this->floatingText->updateMovement();
		}

		return parent::onUpdate($currentTick);
	}

	public function setDisplayBlock(int $id, int $damage = 0) : void{
		if($id !== 0){
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, RuntimeBlockMapping::toStaticRuntimeId($id, $damage));
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_OFFSET, 6);
			$this->getNetworkProperties()->setByte(EntityMetadataProperties::MINECART_HAS_DISPLAY, 1);
		}else{
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, 0);
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_OFFSET, 0);
			$this->getNetworkProperties()->setByte(EntityMetadataProperties::MINECART_HAS_DISPLAY, 0);
		}
	}

	public function findFloatingText() : ?FloatingText{
		/** @var FloatingText $entity */
		$entity = $this->getWorld()->getNearestEntity($this->location, 4, FloatingText::class);
		return $entity ?? null;
	}

	public function isOpening() : bool{
		return $this->openedPlayer !== null;
	}

	public function lookAt(Vector3 $target) : void{
		$xDist = $target->x - $this->location->x;
		$zDist = $target->z - $this->location->z;
		$this->location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($this->location->yaw < 0){
			$this->location->yaw += 360.0;
		}
		$this->location->yaw -= 180;

		$sayi = [PHP_INT_MAX, 360];
		foreach([90, 180, 270, 360] as $int){
			$sub = abs($int - $this->location->yaw);
			if($sub < $sayi[0]){
				$sayi = [$sub, $int];
			}
		}
		$this->location->yaw = $sayi[1];
	}

	public function getCrateTier() : string{
		return $this->content->getName();
	}

	/**
	 * @return CrateContents
	 */
	public function getContent() : CrateContents{
		return $this->content;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setString("CrateTier", $this->getCrateTier());
		return $nbt;
	}
}