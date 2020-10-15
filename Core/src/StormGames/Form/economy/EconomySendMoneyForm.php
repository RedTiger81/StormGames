<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\economy;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Label;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\EconomyForm;
use StormGames\Prefix;

class EconomySendMoneyForm extends CustomForm{

    /** @var SGPlayer[] */
    private $onlinePlayers = [];

    public function __construct(SGPlayer $player){
        /** @var SGPlayer $onlinePlayer */
        foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer){
            if($onlinePlayer->getLowerCaseName() !== $player->getLowerCaseName()){
                $this->onlinePlayers[$onlinePlayer->getName()] = $onlinePlayer;
            }
        }

        if(empty($this->onlinePlayers)){
            $elements = [
                new Label('label', TextFormat::RED . $player->translate('error.generic.noPlayer'))
            ];
        }else{
	        $elements = [
		        new Dropdown('targets', $player->translate('default.target'), array_keys($this->onlinePlayers)),
		        new Input('amount', $player->translate('default.amount'))
	        ];
        }

        parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.economy.sendMoney')), $elements);
    }

    public function onSubmit(Player $player, CustomFormResponse $data) : void{
        if(count($this->getAllElements()) === 1) return;

        /** @var SGPlayer $player */
        /** @var SGPlayer $target */
        /** @noinspection PhpUndefinedMethodInspection */
        $name = $this->getElement(0)->getOption($data->getInt('targets'));
        $target = $this->onlinePlayers[$name] ?? null;
        if($target !== null and $target->isOnline()){
            $amount = (int) $data->getString('amount');
            if($player->reduceMoney($amount, false)){
                if($target->addMoney($amount)){
                    $player->sendMessage(Prefix::ECONOMY() . $player->translate("forms.economy.success", [TextFormat::GREEN . $amount . TextFormat::GRAY, TextFormat::GREEN . $target->getName() . TextFormat::GRAY]));
                    $target->sendMessage(Prefix::ECONOMY() . $target->translate("forms.economy.success.target", [TextFormat::GREEN . $player->getName() . TextFormat::GRAY, TextFormat::GREEN . $amount . TextFormat::GRAY]));
                }else{
                    $player->addMoney($amount, false);
                    $player->sendMessage(Prefix::ECONOMY() . TextFormat::RED . $player->translate("forms.economy.reachLimit"));
                }
            }else{
                $player->sendMessage(Prefix::ECONOMY() . TextFormat::RED . $player->translate('error.generic.noMoney'));
            }
        }else{
            $player->sendMessage(Prefix::ECONOMY() . TextFormat::RED . $player->translate('error.generic.playerIsOffline', [$name]));
        }
    }

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new EconomyForm($player));
	}
}