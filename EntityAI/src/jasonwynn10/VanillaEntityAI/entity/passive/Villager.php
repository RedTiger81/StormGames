<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\passive;

use jasonwynn10\VanillaEntityAI\entity\AgeableTrait;
use jasonwynn10\VanillaEntityAI\entity\Collidable;
use jasonwynn10\VanillaEntityAI\entity\CollisionCheckingTrait;
use jasonwynn10\VanillaEntityAI\entity\CreatureBase;
use jasonwynn10\VanillaEntityAI\entity\Interactable;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

class Villager extends CreatureBase implements Collidable, Interactable {
	use AgeableTrait, CollisionCheckingTrait;
	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	public const NETWORK_ID = EntityLegacyIds::VILLAGER;

	public $width = 0.6;
	public $height = 1.8;

	/** @var int */
	private $profession = self::PROFESSION_FARMER;

	public function getName() : string{
		return "Villager";
	}

	public function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		/** @var int $profession */
		$profession = $nbt->getInt("Profession", self::PROFESSION_FARMER);
		$this->baby = (bool) $nbt->getByte("Baby", 0);

		if($this->baby) $this->setBaby(true);

		if($profession > 4 or $profession < 0){
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt("Profession", $this->getProfession());
		$nbt->setByte('Baby', (int) $this->baby);

		return $nbt;
	}

	/**
	 * Sets the villager profession
	 */
	public function setProfession(int $profession) : void{
		$this->profession = $profession; //TODO: validation
	}

	public function getProfession() : int{
		return $this->profession;
	}

	protected function syncNetworkData() : void{
		parent::syncNetworkData();
		$this->networkProperties->setGenericFlag(EntityMetadataFlags::BABY, $this->baby);

		$this->networkProperties->setInt(EntityMetadataProperties::VARIANT, $this->profession);
	}

	/**
	 * @param Entity $entity
	 */
	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
	}

	public function onCollideWithBlock(Block $block) : void {
		// TODO: Implement onCollideWithBlock() method.
	}

	/**
	 * @param CreatureBase $source
	 */
	public function push(CreatureBase $source) : void {
		// TODO: Implement push() method.
	}

	public function onPlayerInteract(Player $player) : void {
		// TODO: Implement onPlayerInteract() method.
	}
}