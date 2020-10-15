<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\neutral;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\World as Level;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\player\Player;
use pocketmine\utils\Random;

class FireworksRocket extends Projectile {
	public const NETWORK_ID = EntityLegacyIds::FIREWORKS_ROCKET;
	/** @var float */
	public $width = 0.25;
	/** @var float */
	public $height = 0.25;
	/** @var float */
	public $gravity = 0.0;
	/** @var float */
	public $drag = 0.01;
	/** @var int */
	private $lifeTime = 0;
	/** @var \pocketmine\item\Item */
	private $item;
	/** @var null|Random */
	private $random;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, \pocketmine\item\Item $item = null, ?Random $random = null) {
		$this->random = $random;
		$this->item = $item;
		parent::__construct($level, $nbt, $shootingEntity);
	}

	public function spawnTo(Player $player) : void {
		$this->setMotion($this->getDirectionVector());
		$this->getWorld()->broadcastLevelEvent($this->location, LevelSoundEventPacket::SOUND_LAUNCH);
		parent::spawnTo($player);
	}

	public function despawnFromAll() : void {
		$this->broadcastEntityEvent(ActorEventPacket::FIREWORK_PARTICLES);
		parent::despawnFromAll();
		$this->getWorld()->broadcastLevelEvent($this->location, LevelSoundEventPacket::SOUND_BLAST);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->lifeTime-- < 0) {
			$this->flagForDespawn();
			return true;
		}else {
			return parent::entityBaseTick($tickDiff);
		}
	}

	protected function initEntity(CompoundTag $nbt) : void {
		parent::initEntity($nbt);
		$random = $this->random ?? new Random;
		$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, true);
		$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, true);
		$this->getNetworkProperties()->setCompoundTag(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, $this->item->getNamedTag());
		$flyTime = 1;
		if($nbt->hasTag("Fireworks")) {
			$flyTime = $nbt->getCompoundTag("Fireworks")->getByte("Flight", 1);
		}
		$this->lifeTime = 20 * $flyTime + $random->nextBoundedInt(5) + $random->nextBoundedInt(7);
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void {
	}
}