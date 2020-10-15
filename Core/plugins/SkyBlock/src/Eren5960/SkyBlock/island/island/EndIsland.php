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
 * @date 28 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\island\island;

use Eren5960\SkyBlock\SkyPlayer;
use jasonwynn10\VanillaEntityAI\entity\hostile\Enderman;
use jasonwynn10\VanillaEntityAI\entity\hostile\Endermite;
use jasonwynn10\VanillaEntityAI\entity\passive\Cow;
use jasonwynn10\VanillaEntityAI\entity\passive\Sheep;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class EndIsland extends IslandBase{
	public function getCobblestoneId() : int{
		return BlockLegacyIds::END_STONE;
	}

	/**
	 * @ignoreCancelled true
	 *
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event) : void{
		$player = $event->getEntity();
		if($player instanceof SkyPlayer && $this->inIsland($player)){
			if($event->getBaseDamage() >= $player->getHealth()){
				$event->setCancelled();
			}
			if($event->getCause() === EntityDamageEvent::CAUSE_VOID){
				$player->teleport($this->getWorld()->getSpawnLocation());
			}
		}
	}

	public function doChestContents(ChestInventory $inventory) : void{
		$inventory->setContents([
			ItemFactory::get(ItemIds::LAVA), ItemFactory::get(ItemIds::WATER),
			ItemFactory::get(ItemIds::SPAWN_EGG, Enderman::NETWORK_ID, 2), ItemFactory::get(ItemIds::SPAWN_EGG, Endermite::NETWORK_ID),
			ItemFactory::get(ItemIds::TOTEM, 0, 2)
		]);
	}
}