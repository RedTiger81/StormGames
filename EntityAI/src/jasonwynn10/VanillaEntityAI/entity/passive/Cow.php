<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\passive;

use jasonwynn10\VanillaEntityAI\entity\AnimalBase;
use jasonwynn10\VanillaEntityAI\entity\Interactable;
use pocketmine\item\Bucket;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

class Cow extends AnimalBase implements Interactable {
	public const NETWORK_ID = EntityLegacyIds::COW;
	public $width = 1.5;
	public $height = 1.2;

	public function initEntity(CompoundTag $nbt) : void {
		$this->setMaxHealth(10);
		parent::initEntity($nbt);
	}

	/*public function onUpdate(int $currentTick) : bool {
		if($this->closed){
			return false;
		}
		/*$tickDiff = $currentTick - $this->lastUpdate;
		if($this->attackTime > 0) {
			//$this->move($this->motion->x * $tickDiff, $this->motion->y, $this->motion->z * $tickDiff);
			//$this->motion->y -= 0.2 * $tickDiff;
			//$this->updateMovement();
			return parent::onUpdate($currentTick);
		}

		return parent::onUpdate($currentTick);
	}*/

	/**
	 * @return Item[]
	 */
	public function getDrops() : array {
		$drops = parent::getDrops();
		if(!$this->isBaby()) {
			if($this->isOnFire()) {
				$drops[] = ItemFactory::get(ItemIds::COOKED_BEEF, 0, mt_rand(1, 3));
			}else{
				$drops[] = ItemFactory::get(ItemIds::RAW_BEEF, 0, mt_rand(1, 3));
			}
			$drops[] = ItemFactory::get(ItemIds::LEATHER, 0, mt_rand(0, 2));
			return $drops;
		}else {
			return $drops;
		}
	}

	/**
	 * @return int
	 */
	public function getXpDropAmount() : int {
		$exp = parent::getXpDropAmount();
		if(!$this->isBaby()) {
			$exp += mt_rand(1, 3);
			return $exp;
		}
		return $exp;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Cow";
	}

	public function onPlayerLook(Player $player) : void {
		$hand = $player->getInventory()->getItemInHand();
		if($hand instanceof Bucket and $hand->getMeta() === 0) { // check for empty bucket
			$player->getNetworkProperties()->setString(EntityMetadataProperties::INTERACTIVE_TAG, "İneği Sağ");
		}
	}

	public function onPlayerInteract(Player $player) : void {
		$hand = $player->getInventory()->getItemInHand();
		if($hand instanceof Bucket and $hand->getMeta() === 0) { // check for empty bucket
			$item = ItemFactory::get(ItemIds::BUCKET, 1);
			if($player->isSurvival()){
				if($hand->getCount() === 1){
					$player->getInventory()->setItemInHand($item);
				}else{
					$player->getInventory()->setItemInHand($hand->setCount($hand->getCount() - 1));
					$player->getInventory()->addItem($item);
				}
			}else{
				$player->getInventory()->addItem($item);
			}
			$this->location->world->broadcastLevelEvent($player->getPosition(), LevelSoundEventPacket::SOUND_MILK, 0);
		}
	}
}