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
 * @date 30 Mart 2020
 */
declare(strict_types=1);

namespace StormGames\SGCore\blocks;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\Transparent;
use pocketmine\item\ItemIds;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use StormGames\SGCore\tiles\Beacon;

class BeaconBlock extends Transparent{
	public function __construct(int $meta = 0){
		parent::__construct(new BlockIdentifier(BlockLegacyIds::BEACON, $meta, ItemIds::BEACON, Beacon::class), "Beacon", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 15.0));
	}

	public function getLightLevel() : int{
		return 15;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$world = $this->getPos()->getWorld();
			$beacon = $world->getTile($this->getPos());

			if($beacon === null){
				/** @var Beacon $hopper */
				$beacon = TileFactory::create(Beacon::class, $world, $this->getPos());
				$world->addTile($beacon);
			}

			$top = $this->getSide(Facing::UP);
			if($top->isTransparent() !== true){
				return true;
			}

			$player->setCurrentWindow($beacon->getInventory());
		}

		return true;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$value = parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);

		if($value){
			$tile = TileFactory::create(Beacon::class, $this->getPos()->getWorld(), $this->getPos());
			$this->getPos()->getWorld()->addTile($tile);
		}

		return $value;
	}

	public function onScheduledUpdate() : void{
		$beacon = $this->pos->getWorld()->getTile($this->pos);
		if($beacon instanceof Beacon && $beacon->onUpdate()){
			$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, 1); //TODO: check this
		}
	}
}