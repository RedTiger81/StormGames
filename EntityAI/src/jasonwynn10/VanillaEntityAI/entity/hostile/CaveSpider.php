<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity\hostile;

use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;

class CaveSpider extends Spider {
	public const NETWORK_ID = EntityLegacyIds::CAVE_SPIDER;
	public $width = 1.438;
	public $height = 0.547;

	/**
	 * @return string
	 */
	public function getName() : string {
		return "Cave Spider";
	}
}