<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;



use pocketmine\player\Player;

interface Interactable {
	public function onPlayerInteract(Player $player) : void;
}