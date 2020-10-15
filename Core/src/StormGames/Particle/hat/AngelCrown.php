<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle\hat;

use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\GenericParticle;
use StormGames\Particle\Hat;
use StormGames\Particle\Particle;
use StormGames\SGCore\lang\Language;

class AngelCrown extends Particle implements Hat{

	public function createParticle(Player $player, int $currentTick) : void{
		$this->pos->setWorld($player->getWorld());
		$this->pos->setComponents($player->getPosition()->x, $player->getPosition()->y + $player->getEyeHeight() + 0.25, $player->getPosition()->z);

		$particle = new GenericParticle(ParticleIds::END_ROD);
		for($i = 0; $i < 12; $i++){
			$degRad = deg2rad(30 * $i);
			$this->pos->getWorld()->addParticle($this->pos->add(0.35 * cos($degRad), 0, 0.35 * sin($degRad)), $particle);

			if($i < 5){
				$this->pos->getWorld()->addParticle($this->pos->add((lcg_value() - 0.5) / 10, 0.46, (lcg_value() - 0.5) / 10), $particle);
			}
		}
	}

	public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string{
		return TextFormat::GRAY . Language::translate($locale, "particles.angelcrown");
	}
}