<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class ZombieVillager extends Zombie {
	public const NETWORK_ID = EntityLegacyIds::ZOMBIE_VILLAGER;
	public $width = 1.031;
	public $height = 2.125;

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Zombie Villager";
	}
}