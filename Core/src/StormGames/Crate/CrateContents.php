<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Crate;

use pocketmine\form\Form;
use StormGames\Form\BuyCrateKeyForm;
use StormGames\SGCore\SGPlayer;

abstract class CrateContents{

    /** @var CrateContents[] */
    private static $contents = [];

    public static function init() : void{
        self::addCrateContent(new VoteCrateContents());
        self::addCrateContent(new NormalCrateContents());
	    self::addCrateContent(new BuilderCrateContents());
	    self::addCrateContent(new StormCrateContents());
    }

    public static function addCrateContent(CrateContents $contents){
        self::$contents[$contents->getName()] = $contents;
    }

    public static function getCrateContent(string $content) : ?CrateContents{
        return clone self::$contents[$content] ?? null;
    }

    abstract public function getName() : string;

    abstract public function giveRandomContent(SGPlayer $player, string &$contentName) : void;

    abstract public function getPrice() : int;

    abstract public function getBlockId(): int;

    public function getBuyForm(SGPlayer $player) : Form{
        return new BuyCrateKeyForm($player, $this->getName(), $this->getPrice());
    }
}