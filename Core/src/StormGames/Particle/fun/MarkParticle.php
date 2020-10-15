<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle\fun;

use pocketmine\world\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Particle\Fun;
use StormGames\Particle\Particle;
use StormGames\SGCore\lang\Language;

class MarkParticle extends Particle implements Fun{

	public const DEG_2_RAD = 0.087266462599716;

	public function createParticle(Player $player, int $currentTick) : void{
		$y = $player->getPosition()->y + $player->getEyeHeight() + 1;

		$t = 0.25; //lower radius
		$cos = cos(self::DEG_2_RAD);
		$sin = sin(self::DEG_2_RAD);
		for($yaw = 0, $cy = $y; $cy < $y + 2; $yaw += (M_PI * 2) / 25, $cy += 0.02, $t += 0.01){
			$diffx = -sin($yaw) * $t;
			$diffz = cos($yaw) * $t;
			$rx = $diffx * $cos + $diffz * $sin;
			$rz = -$diffx * $sin + $diffz * $cos;
			$player->getWorld()->addParticle(new Vector3($player->getPosition()->x + $rx, $cy, $player->getPosition()->z + $rz), new FlameParticle());
		}
	}

	public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string{
		return TextFormat::GOLD . Language::translate($locale, "particles.mark");
	}
}