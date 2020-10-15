<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class Stray extends Skeleton {
	public const NETWORK_ID = EntityLegacyIds::STRAY;

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Stray";
	}
}