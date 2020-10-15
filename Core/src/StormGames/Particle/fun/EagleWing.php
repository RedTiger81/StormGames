<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle\fun;

use pocketmine\world\particle\SmokeParticle;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Particle\Fun;
use StormGames\Particle\Particle;
use StormGames\SGCore\lang\Language;

class EagleWing extends Particle implements Fun{

    /** @var Vector3[] */
    private $outline;
    /** @var SmokeParticle */
    private $particle;

    public function __construct(){
        parent::__construct();

        $this->particle = new SmokeParticle();

        $this->outline = [
            new Vector3(0, 0, -0.5),
            new Vector3(0.1, 0.01, -0.5),
            new Vector3(0.3, 0.03, -0.5),
            new Vector3(0.4, 0.04, -0.5),
            new Vector3(0.6, 0.1, -0.5),
            new Vector3(0.61, 0.2, -0.5),
            new Vector3(0.62, 0.4, -0.5),
            new Vector3(0.63, 0.6, -0.5),
            new Vector3(0.635, 0.7, -0.5),
            new Vector3(0.7, 0.7, -0.5),
            new Vector3(0.9, 0.75, -0.5),
            new Vector3(1.2, 0.8, -0.5),
            new Vector3(1.4, 0.9, -0.5),
            new Vector3(1.6, 1, -0.5),
            new Vector3(1.8, 1.1, -0.5),
            new Vector3(1.85, 0.9, -0.5),
            new Vector3(1.9, 0.7, -0.5),
            new Vector3(1.85, 0.5, -0.5),
            new Vector3(1.8, 0.3, -0.5),
            new Vector3(1.75, 0.1, -0.5),
            new Vector3(1.7, -0.1, -0.5),
            new Vector3(1.65, -0.3, -0.5),
            new Vector3(1.55, -0.5, -0.5),
            new Vector3(1.45, -0.7, -0.5),
            new Vector3(1.30, -0.75, -0.5),
            new Vector3(1.15, -0.8, -0.5),
            new Vector3(1.0, -0.85, -0.5),
            new Vector3(0.8, -0.87, -0.5),
            new Vector3(0.6, -0.7, -0.5),
            new Vector3(0.5, -0.5, -0.5),
            new Vector3(0.4, -0.3, -0.5),
            new Vector3(0.3, -0.3, -0.5),
            new Vector3(0.15, -0.3, -0.5),
            new Vector3(0, -0.3, -0.5),

            //
            new Vector3(0.9, 0.55, -0.5),
            new Vector3(1.2, 0.6, -0.5),
            new Vector3(1.4, 0.7, -0.5),
            new Vector3(1.6, 0.9, -0.5),
            //
            new Vector3(0.9, 0.35, -0.5),
            new Vector3(1.2, 0.4, -0.5),
            new Vector3(1.4, 0.5, -0.5),
            new Vector3(1.6, 0.7, -0.5),
            //
            new Vector3(0.9, 0.15, -0.5),
            new Vector3(1.2, 0.2, -0.5),
            new Vector3(1.4, 0.3, -0.5),
            new Vector3(1.6, 0.5, -0.5),
            //
            new Vector3(0.9, -0.05, -0.5),
            new Vector3(1.2, 0, -0.5),
            new Vector3(1.4, 0.1, -0.5),
            new Vector3(1.6, 0.3, -0.5),
            //
            new Vector3(0.7, -0.25, -0.5),
            new Vector3(1.0, -0.2, -0.5),
            new Vector3(1.2, -0.1, -0.5),
            new Vector3(1.4, 0.1, -0.5),
            //
            new Vector3(0.7, -0.45, -0.5),
            new Vector3(1.0, -0.4, -0.5),
            new Vector3(1.2, -0.3, -0.5),
            new Vector3(1.4, -0.1, -0.5),
            //
            new Vector3(1.30, -0.55, -0.5),
            new Vector3(1.15, -0.6, -0.5),
            new Vector3(1.0, -0.65, -0.5)
        ];
    }

    public function createParticle(Player $player, int $currentTick) : void{
        $this->pos->setWorld($player->getWorld());
        $this->pos->setComponents($player->getPosition()->x, $player->getPosition()->y + $player->getEyeHeight() - 0.2, $player->getPosition()->z);
        $rot = -$player->getLocation()->getYaw() * (M_PI / 180);

        foreach($this->outline as $point){
            $rotated = $this->rotate($point, $rot);

            $player->getWorld()->addParticle($this->pos->add($rotated), $this->particle);

            $point->z *= -1;
            $rotated = $this->rotate($point, $rot + M_PI);
            $point->z *= -1;

            $player->getWorld()->addParticle($this->pos->add($rotated), $this->particle);
        }
    }

    public function rotate(Vector3 $pos, float $rot){
        $cos = cos($rot);
        $sin = sin($rot);

        return new Vector3(
            ($pos->x * $cos + $pos->z * $sin),
            $pos->y,
            ($pos->x * -$sin + $pos->z * $cos)
        );
    }

    public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string{
        return TextFormat::GRAY . Language::translate($locale, "particles.eaglewing");
    }
}