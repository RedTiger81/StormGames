<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\block;

use jasonwynn10\VanillaEntityAI\entity\passiveaggressive\SnowGolem;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\CarvedPumpkin;
use pocketmine\block\Snow;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Pumpkin extends CarvedPumpkin {
	public function __construct(){
		parent::__construct(new BlockIdentifier(BlockLegacyIds::CARVED_PUMPKIN, 0, ItemIds::CARVED_PUMPKIN), "Carved Pumpkin", new BlockBreakInfo(0));
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $vector, Player $player = null) : bool{
		if(($block1 = $this->getSide(Facing::DOWN, 1)) instanceof Snow and ($block2 = $this->getSide(Facing::DOWN, 2)) instanceof Snow) {
			$this->getPos()->getWorld()->setBlock($this->getPos(), BlockFactory::get(BlockLegacyIds::AIR));
			$this->getPos()->getWorld()->setBlock($block1->getPos(), BlockFactory::get(BlockLegacyIds::AIR));
			$this->getPos()->getWorld()->setBlock($block2->getPos(), BlockFactory::get(BlockLegacyIds::AIR));
			$entity = EntityFactory::create(SnowGolem::class, $this->getPos()->getWorld(), EntityFactory::createBaseNBT($block2->getPos()->add(0.5,0,0.5)));
			$entity->spawnToAll();
			return false;
		}elseif(($block1 = $this->getSide(Facing::NORTH, 1)) instanceof Snow and ($block2 = $this->getSide(Facing::NORTH, 2)) instanceof Snow) {
			$this->createSnowgolem($block1, $block2);
			return false;
		}elseif(($block1 = $this->getSide(Facing::EAST, 1)) instanceof Snow and ($block2 = $this->getSide(Facing::EAST, 2)) instanceof Snow) {
			$this->createSnowgolem($block1, $block2);
			return false;
		}elseif(($block1 = $this->getSide(Facing::SOUTH, 1)) instanceof Snow and ($block2 = $this->getSide(Facing::SOUTH, 2)) instanceof Snow) {
			$this->createSnowgolem($block1, $block2);
			return false;
		}elseif(($block1 = $this->getSide(Facing::WEST, 1)) instanceof Snow and ($block2 = $this->getSide(Facing::WEST, 2)) instanceof Snow) {
			$this->createSnowgolem($block1, $block2);
			return false;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $vector, $player);
	}

	public function createSnowgolem(Block $block1, Block $block2): void{
		$this->getPos()->getWorld()->setBlock($this->getPos(), $air = BlockFactory::get(BlockLegacyIds::AIR));
		$this->getPos()->getWorld()->setBlock($block1->getPos(), BlockFactory::get(BlockLegacyIds::AIR));
		$this->getPos()->getWorld()->setBlock($block2->getPos(), BlockFactory::get(BlockLegacyIds::AIR));
		$entity = EntityFactory::create(SnowGolem::class, $this->getPos()->getWorld(), EntityFactory::createBaseNBT($this->getPos()->add(0.5,0,0.5)));
		$entity->spawnToAll();
	}
}