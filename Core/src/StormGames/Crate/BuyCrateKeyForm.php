<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Crate;

use pocketmine\form\CustomFormResponse;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\FCPlayer;
use StormGames\SGCore\Main;
use StormGames\Prefix;

class BuyCrateKeyForm extends \StormGames\Form\BuyCrateKeyForm{

    protected $priceTranslated = Main::MONETARY_UNIT;

    public function onSubmit(Player $player, CustomFormResponse $data) : void{
        /** @var FCPlayer $player */
        $amount = (int) $data->getFloat('amount');
        $price = $this->price * $amount;

        if($player->reduceMoney($price)){
            $player->addCrateKeys($this->tier, $amount);
            $player->sendMessage(Prefix::MAIN . TextFormat::GREEN . $player->translate("forms.buyCrateKey.bought", [TextFormat::WHITE . $amount . TextFormat::GREEN, TextFormat::WHITE . $price . TextFormat::GREEN]));
        }else{
            $player->sendMessage(Prefix::MAIN . TextFormat::RED . $player->translate("error.generic.noMoney"));
        }
    }
}