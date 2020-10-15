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
use pocketmine\item\ItemIds;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use StormGames\SGCore\tiles\Hopper;

class HopperBlock extends \pocketmine\block\Hopper{
	public function __construct(int $meta = 0){
		parent::__construct(new BlockIdentifier(BlockLegacyIds::HOPPER_BLOCK, $meta, ItemIds::HOPPER, Hopper::class), "Hopper", new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 15.0));
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$world = $this->getPos()->getWorld();
			$hopper = $world->getTile($this->getPos());

			if($hopper === null){
				/** @var Hopper $hopper */
				$hopper = TileFactory::create(Hopper::class, $world, $this->getPos());
				$world->addTile($hopper);
			}

			if(!$hopper->canOpenWith($item->getCustomName())){
				return true;
			}

			$player->setCurrentWindow($hopper->getInventory());
		}

		return true;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$value = parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);

		if($value){
			$tile = TileFactory::create(Hopper::class, $this->getPos()->getWorld(), $this->getPos());
			$this->getPos()->getWorld()->addTile($tile);
		}

		return $value;
	}

	public function onScheduledUpdate() : void{
		$hopper = $this->pos->getWorld()->getTile($this->pos);
		if($hopper instanceof Hopper && $hopper->onUpdate()){
			$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, 1); //TODO: check this
		}
	}
}