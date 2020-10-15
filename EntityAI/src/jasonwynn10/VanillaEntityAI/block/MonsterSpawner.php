<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\block;

use Eren5960\SkyBlock\SkyPlayer;
use jasonwynn10\VanillaEntityAI\tile\MobSpawner;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\SpawnEgg;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use StormGames\SGCore\utils\Utils;

class MonsterSpawner extends \pocketmine\block\MonsterSpawner {
	public function __construct(){
		parent::__construct(new BlockIdentifier(BlockLegacyIds::MONSTER_SPAWNER, 0, ItemIds::MONSTER_SPAWNER), "Monster Spawn", null);
	}

	/**
	 * @return bool
	 */
	public function canBeReplaced() : bool {
		return false;
	}

	/**
	 * @param Item        $item
	 * @param int         $face
	 * @param Vector3     $clickVector
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool {
		$spawner = $this->getPos()->getWorld()->getTile($this->getPos());
		if($player instanceof SkyPlayer) {
			if($item instanceof SpawnEgg){
				if(!$spawner instanceof MobSpawner) {
					/** @var MobSpawner $spawner */
					$spawner = new MobSpawner($this->getPos()->getWorld(), $this->getPos(), $player->getSpawnerTime());
					$this->getPos()->getWorld()->addTile($spawner);
				}
				$spawner->setEntityId($item->getMeta());
				$player->getInventory()->setItemInHand($item->pop());
			}else{
				if(!$spawner instanceof MobSpawner){
					$player->sendPopup("§cAktif değil");
				}else{
					$player->sendPopup("§7" . Utils::secondsToDateInterval(intval(floor($spawner->delay / 20)))->format("§b%i §7dakika, §b%s §7saniye") . " kaldı" . ($spawner->minSpawnDelay > 300*20 ? "\n» §aPaket satın alarak hızını yükseltebilirsin §8«" : ""));
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * @return int
	 */
	public function getLightLevel() : int {
		return 3;
	}

	public function onScheduledUpdate() : void{
		$mobspawner = $this->pos->getWorld()->getTile($this->pos);
		if($mobspawner instanceof MobSpawner and $mobspawner->onUpdate()){
			$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, 1); //TODO: check this
		}
	}
}