<?php
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\world\sound\LaunchSound;
use StormGames\GenericSound;
use StormGames\SGCore\item\Firework;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\World;

class FireworkRocket extends Entity{
	public const NETWORK_ID = EntityLegacyIds::FIREWORKS_ROCKET;
	public $width = 0.25;
	public $height = 0.25;
	/** @var int */
	protected $lifeTime = 0;
	public $item;

	public function __construct(World $level, CompoundTag $nbt, ?Firework $firework = null){
		parent::__construct($level, $nbt);
		if($firework !== null && $firework->getNamedTag()->hasTag('Firework', CompoundTag::class)){
			$this->item = $firework;
			$this->setLifeTime($firework->getRandomizedFlightDuration());
		}else{
			$this->item = new Firework();
		}
		$level->addSound($this->location, new LaunchSound());
	}

	protected function tryChangeMovement() : void{
		$this->motion->x *= 1.15;
		$this->motion->y += 0.04;
		$this->motion->z *= 1.15;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if($this->doLifeTimeTick()) {
			$hasUpdate = true;
		}
		return $hasUpdate;
	}

	public function setLifeTime(int $life) : void{
		$this->lifeTime = $life;
	}

	protected function doLifeTimeTick() : bool{
		if(!$this->isFlaggedForDespawn() and --$this->lifeTime < 0) {
			$this->doExplosionAnimation();
			$this->flagForDespawn();
			return true;
		}
		return false;
	}

	protected function doExplosionAnimation() : void{
		/** @var Item $firework */
		$firework = $this->item;
		if($firework === null){
			return;
		}
		$fireworkNBT = $firework->getNamedTag()->getCompoundTag("Firework");
		if($fireworkNBT === null){
			return;
		}
		$explosions = $fireworkNBT->getListTag('Explosions');
		if($explosions === null){
			return;
		}
		static $explosionType = [
			Firework::TYPE_SMALL_SPHERE => LevelSoundEventPacket::SOUND_BLAST,
			Firework::TYPE_HUGE_SPHERE => LevelSoundEventPacket::SOUND_LARGE_BLAST,
			Firework::TYPE_STAR => LevelSoundEventPacket::SOUND_TWINKLE,
			Firework::TYPE_BURST => LevelSoundEventPacket::SOUND_TWINKLE,
			Firework::TYPE_CREEPER_HEAD => LevelSoundEventPacket::SOUND_TWINKLE
		];
		/** @var CompoundTag $explosion */
		foreach($explosions->getAllValues() as $explosion){
			$soundClass = $explosionType[$explosion->getByte('FireworkType', Firework::TYPE_SMALL_SPHERE)] ?? $explosionType[Firework::TYPE_SMALL_SPHERE];
			$this->location->getWorld()->addSound($this->location, new GenericSound($soundClass));
		}
		$this->broadcastEntityEvent(ActorEventPacket::FIREWORK_PARTICLES);
	}

	public function syncNetworkData() : void{
		$this->getNetworkProperties()->setCompoundTag(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, $this->item->getNamedTag());
	}
}