<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle\hat;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\player\Player;
use pocketmine\utils\Color;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\RedstoneParticle;
use StormGames\GenericParticle;
use StormGames\Particle\Hat;
use StormGames\Particle\Particle;
use StormGames\SGCore\lang\Language;

class ChristmasHat extends Particle implements Hat{

	public function createParticle(Player $player, int $currentTick) : void{
		$this->pos->setWorld($player->getWorld());
		$this->pos->setComponents($player->getPosition()->x, $player->getPosition()->y + $player->getEyeHeight() + 0.25, $player->getPosition()->z);

		for($i = 0; $i < 12; $i++){
			$degRad = deg2rad(30 * $i);
			$this->sendParticle($this->pos->add(0.35 * ($cos = cos($degRad)), 0, 0.35 * ($sin = sin($degRad))), false);
			$this->sendParticle($this->pos->add(0.25 * $cos, 0.1, 0.25 * $sin));
			$this->sendParticle($this->pos->add(0.16 * $cos, 0.2, 0.16 * $sin));
			$this->sendParticle($this->pos->add(0.07 * $cos, 0.3, 0.07 * $sin));
			$this->sendParticle($this->pos->add(0.07 * $cos, 0.4, 0.07 * $sin));

			if($i < 5){
				$this->sendParticle($this->pos->add((lcg_value() - 0.5) / 10, 0.46, (lcg_value() - 0.5) / 10), false);
			}
		}
	}

	public function sendParticle(Vector3 $pos, bool $red = true){
	    $this->pos->getWorld()->addParticle($pos, $red ? new DustParticle(new Color(255, 0, 0)) : new GenericParticle(ParticleIds::END_ROD, 0));
	}

	public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string{
		return TextFormat::RED . Language::translate($locale, "particles.christmashat");
	}
}