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
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;

class EconomySellModalForm extends ModalForm{

    /** @var int */
    private $id, $count;

    public function __construct(SGPlayer $player, int $id, int $count = -1){
        $this->id = $id;
        $this->count = $count;

        parent::__construct(
            $player->translate('forms.economy.sell'),
            $player->translate('forms.economy.sell.modal', [$this->money($player)]),
            $player->translate("forms.yes"),
            $player->translate("forms.no")
        );
    }

    private function money(SGPlayer $player) : int{
        if($this->id !== -1){
            return $this->count * EconomySellForm::ITEMS[$this->id];
        }

        $money = 0;
        foreach($player->getInventory()->getContents() as $item){
            if(isset(EconomySellForm::ITEMS[$item->getId()]) and ($item->getMeta() === 0 or !($item instanceof Durable))){
                $money += $item->getCount() * EconomySellForm::ITEMS[$item->getId()];
            }
        }
        return $money;
    }

    public function onSubmit(Player $player, bool $choice) : void{
        /** @var SGPlayer $player */
        if($choice){
            $money = 0;
            if($this->id !== -1){
                $count = $this->count;
                foreach($player->getInventory()->getContents() as $slot => $item){
                    if($count <= 0) break;
                    if($item->getId() === $this->id and ($item->getMeta() === 0 or !($item instanceof Durable))){
                        $amount = min($item->getCount(), $count);
                        $money += $amount * EconomySellForm::ITEMS[$item->getId()];
                        $item->setCount($item->getCount() - $amount);
                        $count -= $amount;
                        $player->getInventory()->setItem($slot, $item);
                    }
                }
            }else{
                foreach($player->getInventory()->getContents() as $slot => $item){
                    if(isset(EconomySellForm::ITEMS[$item->getId()]) and ($item->getMeta() === 0 or !($item instanceof Durable))){
                        $player->getInventory()->clear($slot);
                        $money += $item->getCount() * EconomySellForm::ITEMS[$item->getId()];
                    }
                }
            }

            $player->addMoney($money);
            $player->sendMessage(Prefix::ECONOMY() . TextFormat::GREEN . $player->translate('forms.economy.sell.success', [TextFormat::GRAY . $money . TextFormat::GREEN]));
        }
    }

}