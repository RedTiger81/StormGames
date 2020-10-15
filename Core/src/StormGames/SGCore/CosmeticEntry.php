<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use InvalidArgumentException;
use pocketmine\entity\Skin;
use StormGames\Particle\Particle;
use StormGames\Pet\Pet;
use StormGames\SGCore\entity\utils\Skins;
use const PHP_EOL;

class CosmeticEntry{
    /** @var Particle */
    public $particle = null;
    /** @var string */
    public $cape = '', $oldCape;
    /** @var Pet */
    public $pet;

    /** @var SGPlayer */
    private $player;

    public function __construct(SGPlayer $player){
        $this->player = $player;
        $this->oldCape = $player->getSkin()->getCapeData();
    }

    /**
     * @return string
     */
    public function getCape() : string{
        return $this->cape;
    }

    /**
     * @param string $cape
     */
    public function setCape(string $cape) : void{
    	try{
		    $capeData = Skins::getCape($cape);
		    $this->cape = $capeData !== null ? $cape : '';
		    $capeData = $capeData ?? $this->oldCape;

		    $skin = $this->player->getSkin();
		    $this->player->setSkin(new Skin($skin->getSkinId(), $skin->getSkinData(), $capeData, $skin->getGeometryName(), $skin->getGeometryData()));
		    $this->player->sendSkin();
	    }catch(InvalidArgumentException $exception){
    		$this->player->sendMessage("§7»§c Bir hata oluştuğu için işlem gerçekleştirilemedi.");
    		echo $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
	    }
    }

    /**
     * @return Particle
     */
    public function getParticle() : ?Particle{
        return $this->particle;
    }

    /**
     * @param Particle $particle
     */
    public function setParticle(?Particle $particle) : void{
        if($this->particle !== null){
            $this->particle->onClose();
        }

        $this->particle = $particle;
    }

    /**
     * @return Pet
     */
    public function getPet() : ?Pet{
        return $this->pet;
    }

    /**
     * @param Pet|null $pet
     */
    public function setPet(?Pet $pet) : void{
        if($this->pet !== null){
            $this->pet->close();
        }

        $this->pet = $pet;
    }

    public function update(int $currentTick) : void{
        if($this->particle !== null){
            if($currentTick % $this->particle->getTickRate() === 0){
                $this->particle->createParticle($this->player, $currentTick);
            }
        }
    }

    public function reset() : void{
        $this->setParticle(null);
        $this->setPet(null);
    }
}