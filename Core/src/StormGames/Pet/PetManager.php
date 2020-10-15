<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Pet;

use pocketmine\entity\EntityFactory;
use pocketmine\form\FormIcon;
use pocketmine\form\MenuOption;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\TextUtils;

class PetManager{

    /** @var string[] */
    private static $pets;

    public static function init() : void{
        self::registerPet(Chicken::class);
        self::registerPet(Cow::class);
        self::registerPet(Ocelot::class);
        self::registerPet(Panda::class);
        self::registerPet(Pig::class);
        self::registerPet(Sheep::class);
        self::registerPet(Wolf::class);
    }

    public static function registerPet(string $petClass) : void{
        self::$pets[TextUtils::classStringToName($petClass)] = $petClass;
        EntityFactory::register($petClass, []);
    }

    /**
     * @param SGPlayer $player
     * @param string $petClass
     * @return Pet
     */
    public static function givePet(SGPlayer $player, string $petClass) : Pet{
        /** @var Pet $pet */
        $pet = EntityFactory::create($petClass, $player->getWorld(), EntityFactory::createBaseNBT($player->getPosition()), $player);
        $pet->spawnToAll();

        return $pet;
    }

    public static function getMenuOptions(SGPlayer $player) : array{
        $buttons = [];
        /** @var string|Pet $pet */
        foreach(self::$pets as $pet){
            $color = $pet::canUse($player) ? TextFormat::DARK_RED : TextFormat::DARK_GRAY;
            $buttons[$pet] = new MenuOption($color . $player->translate($pet::getPetName()), $pet::getFormIcon());
        }
        $buttons['remove'] = new MenuOption(TextFormat::RED . $player->translate('pets.remove'), new FormIcon('textures/ui/cancel', FormIcon::IMAGE_TYPE_PATH));

        return $buttons;
    }
}