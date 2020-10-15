<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle\fun;

use pocketmine\world\particle\WaterDripParticle;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Particle\Fun;
use StormGames\Particle\Sphere;
use StormGames\SGCore\lang\Language;

class WaterSphere extends Sphere implements Fun{

	public function createParticle(Player $player, int $currentTick) : void{
		$this->pos->setWorld($player->getWorld());
		$this->pos->setComponents($player->getPosition()->x, $player->getPosition()->y + $player->getEyeHeight(), $player->getPosition()->z);
		$this->createSphere(new WaterDripParticle(), 30, 1.5);
	}

	public function getTickRate() : int{
		return 5;
	}

	public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string{
		return TextFormat::BLUE . Language::translate($locale, "particles.watersphere");
	}

	public function getCoins() : int{
		return 10;
	}
}