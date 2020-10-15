<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class Husk extends Zombie {
	public const NETWORK_ID = EntityLegacyIds::HUSK;
	public $width = 1.031;
	public $height = 2.0;

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Husk";
	}
}