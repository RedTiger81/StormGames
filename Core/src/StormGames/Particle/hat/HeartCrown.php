<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle\hat;

use pocketmine\world\particle\HeartParticle;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Particle\Hat;
use StormGames\Particle\Particle;
use StormGames\SGCore\lang\Language;

class HeartCrown extends Particle implements Hat{

	public function createParticle(Player $player, int $currentTick) : void{
		$this->pos->setWorld($player->getWorld());
		$this->pos->setComponents($player->getPosition()->x, $player->getPosition()->y + $player->getEyeHeight() + 1, $player->getPosition()->z);

		for($yaw = 0, $y = $this->pos->y; $y < $this->pos->y + 1; $yaw += (M_PI * 2) / 20, $y += 1 / 20){
			$x = -sin($yaw) + $this->pos->x;
			$z = cos($yaw) + $this->pos->z;
			$player->getWorld()->addParticle(new Vector3($x, $y, $z), new HeartParticle());
		}
	}

	public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string{
		return TextFormat::RED . Language::translate($locale, "particles.heartcrown");
	}

	public function getTickRate() : int{
		return 10;
	}
}