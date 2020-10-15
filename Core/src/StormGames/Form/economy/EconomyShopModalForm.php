<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\economy;

use pocketmine\form\ModalForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;

class EconomyShopModalForm extends ModalForm{

    /** @var EconomyShopBuyForm */
    private $buyForm;

    public function __construct(SGPlayer $player, EconomyShopBuyForm $buyForm){
        $this->buyForm = $buyForm;
        parent::__construct(
            sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.economy.shop.buy')),
            TextFormat::WHITE . $player->translate('forms.economy.shop.buy.modal', [
                TextFormat::GOLD . 'x' . $buyForm->item->getCount() . ' ' . $buyForm->item->getVanillaName() . TextFormat::WHITE,
                TextFormat::GOLD . Utils::addMonetaryUnit($buyForm->price) . TextFormat::WHITE,
            ]),
            TextFormat::GREEN . $player->translate('forms.economy.shop.buy'),
            TextFormat::RED . $player->translate('forms.cancel'));
    }

    public function onSubmit(Player $player, bool $choice) : void{
        if($choice){
            /** @var SGPlayer $player */
            $this->buyForm->buy($player);
        }
    }
}