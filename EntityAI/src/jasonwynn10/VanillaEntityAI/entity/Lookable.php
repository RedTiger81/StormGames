<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\player\Player;

interface Lookable {

	/**
	 * @param Player $player
	 */
	public function onPlayerLook(Player $player) : void;
}