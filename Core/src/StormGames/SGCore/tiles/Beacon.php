<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 02 Nisan 2020
 */
declare(strict_types=1);

namespace StormGames\SGCore\tiles;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\TileFactory;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\inventory\InventoryHolder;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World;
use StormGames\SGCore\inventory\BeaconInventory;

class Beacon extends Spawnable implements Nameable, InventoryHolder{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_PRIMARY = "primary";
	public const TAG_SECONDARY = "secondary";

	/** @var BeaconInventory */
	private $inventory;
	/** @var int */
	private $primary = 0, $secondary = 0;
	/** @var int */
	protected $currentTick = 0;
	/** @var array */
	protected $minerals = [BlockLegacyIds::IRON_BLOCK, BlockLegacyIds::GOLD_BLOCK, BlockLegacyIds::EMERALD_BLOCK, BlockLegacyIds::DIAMOND_BLOCK];
	/** @var AxisAlignedBB */
	protected $rangeBox;

	public function __construct(World $level, Vector3 $pos){
		parent::__construct($level, $pos);

		$this->rangeBox = new AxisAlignedBB($pos->x, $pos->y, $pos->z, $pos->x, $pos->y, $pos->z);
		$this->inventory = new BeaconInventory($this->getPos());
	}

	public function readSaveData(CompoundTag $nbt): void{
		$this->primary = $nbt->getInt(self::TAG_PRIMARY, 0);
		$this->secondary = $nbt->getInt(self::TAG_SECONDARY, 0);

		$this->inventory = new BeaconInventory($this->getPos());

		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_PRIMARY, $this->primary);
		$nbt->setInt(self::TAG_SECONDARY, $this->secondary);

		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
			$this->inventory = null;

			parent::close();
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_PRIMARY, $this->primary);
		$nbt->setInt(self::TAG_SECONDARY, $this->secondary);

		$this->addNameSpawnData($nbt);
	}

	public function getDefaultName() : string{
		return "Beacon";
	}

	public function getInventory() : ?BeaconInventory{
		return $this->inventory;
	}

	public function getRealInventory() : ?BeaconInventory{
		return $this->getInventory();
	}

	public function updateCompoundTag(CompoundTag $nbt) : bool{
		if($nbt->getString("id") !==  TileFactory::getSaveId(static::class)){
			return false;
		}

		$this->primary = $nbt->getInt(self::TAG_PRIMARY);
		$this->secondary = $nbt->getInt(self::TAG_SECONDARY);
		$this->setDirty();
		return true;
	}

	public function onUpdate() : bool{
		if($this->currentTick++ % 80 === 0){
			if(($effectPrim = VanillaEffects::byMcpeId($this->primary)) !== null){
				if(($pyramidLevels = $this->getPyramidLevels()) > 0){
					$duration = 180 + $pyramidLevels * 40;
					$range = (10 + $pyramidLevels * 10);
					$effectPrim = new EffectInstance($effectPrim, $duration, $pyramidLevels == 4 && $this->primary == $this->secondary ? 1 : 0);

					$players = array_filter($this->pos->world->getCollidingEntities($this->rangeBox->expandedCopy($range, $range, $range)), function (Entity $player) : bool{
						return $player instanceof Player and $player->spawned;
					});
					/** @var Player $player */
					foreach($players as $player){
						$player->getEffects()->add($effectPrim);

						if($pyramidLevels == 4 && $this->primary != $this->secondary){
							$regen = new EffectInstance(VanillaEffects::REGENERATION(), $duration);
							$player->getEffects()->add($regen);
						}
					}
				}
			}
		}

		return true;
	}

	protected function getPyramidLevels() : int{
		$allMineral = true;
		for($i = 1; $i < 5; $i++){
			for($x = -$i; $x < $i + 1; $x++){
				for($z = -$i; $z < $i + 1; $z++){
					$allMineral = $allMineral && in_array($this->pos->world->getBlockAt($this->pos->x + $x, $this->pos->y - $i, $this->pos->z + $z)->getId(), $this->minerals);
					if(!$allMineral) return $i - 1;
				}
			}
		}

		return 4;
	}
}