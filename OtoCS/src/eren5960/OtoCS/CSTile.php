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
 * @date 31 Mart 2020
 */
declare(strict_types=1);
 
namespace eren5960\OtoCS;

use pocketmine\block\tile\EnderChest;
use pocketmine\block\tile\NameableTrait;
use pocketmine\inventory\InventoryHolder;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\World;

class CSTile extends EnderChest implements InventoryHolder{
    use NameableTrait;

    public $inventory;
    /** @var string|null */
    public $player = null;

    public function __construct(World $world, Vector3 $pos){
    	parent::__construct($world, $pos);
	    $this->inventory = new CSInventory($this->pos);
    }

	public function getDefaultName() : string{
		return "CSTile";
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function setPlayer(string $player): void{
    	$this->player = $player;
    	$this->inventory->player = $player;
	}

	public function writeSaveData(CompoundTag $nbt) : void {
    	$this->addAdditionalSpawnData($nbt);
	}

	public function readSaveData(CompoundTag $nbt) : void {
		if($nbt->hasTag('player', StringTag::class)) {
			$this->setPlayer($nbt->getString('player'));
		}
		$this->loadName($nbt);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->player !== null){
			$nbt->setString('player', $this->player);
		}
		$this->saveName($nbt);
	}
}