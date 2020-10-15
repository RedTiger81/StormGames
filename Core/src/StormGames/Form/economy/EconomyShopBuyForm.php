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
use pocketmine\form\element\Label;
use pocketmine\form\element\StepSlider;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;

class EconomyShopBuyForm extends CustomForm{

    /** @var Item */
    public $item;
    /** @var int */
    public $price;
    /** @var array */
    public $classInfo;

    public function __construct(SGPlayer $player, array $itemData, array $classInfo){
        $this->item = clone $itemData['item'];
        $this->price = $itemData['price'];
        $this->classInfo = $classInfo;

        $elements = [new Label('label',
			TextFormat::GOLD . $player->translate('forms.economy.shop.buy.text', [
            TextFormat::WHITE . $this->item->getVanillaName() . ' (' . $this->item->getId() . ':' . $this->item->getMeta() . ')' . TextFormat::GOLD,
            TextFormat::WHITE . $this->item->getCount() . TextFormat::GOLD,
            TextFormat::WHITE . Utils::addMonetaryUnit($this->price) . TextFormat::GOLD
        ]))];
        if(!($this->item instanceof Durable)){
            $elements[] = new StepSlider('amount', $player->translate('default.amount'), ['§a1', '§a2', '§24', '§28', '§e16', '§e32', '§664', '§6128', '§c256', '§c512', '§41024', '§42048']);
        }
        parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('forms.economy.shop.buy')), $elements);
    }

    public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$amount = !isset($data->getAll()['amount']) ? 1 : 2 ** $data->getAll()['amount'];
		$this->item->setCount($this->item->getCount() * $amount);
		$this->price = $amount * $this->price;
		if($this->item instanceof Durable){
			$this->buy($player);
		}else{
			$player->sendForm(new EconomyShopModalForm($player, $this));
		}
	}

    public function buy(SGPlayer $player){
        if($player->getInventory()->canAddItem($this->item)){
            if($player->reduceMoney($this->price)){
                $player->getInventory()->addItem($this->item);
                $player->sendMessage(Prefix::ECONOMY() . $player->translate('forms.economy.shop.buy.success', [
                    TextFormat::GREEN . 'x' . $this->item->getCount() . ' ' . $this->item->getVanillaName() . TextFormat::GRAY,
                    TextFormat::GREEN . Utils::addMonetaryUnit($this->price) . TextFormat::GRAY
                ]));
            }else{
                $player->sendMessage(Prefix::ECONOMY() . $player->translate('error.generic.noMoney'));
            }
        }else{
            $player->sendMessage(Prefix::ECONOMY() . $player->translate('error.generic.fullInventory'));
        }
    }

    public function onClose(Player $player) : void{
        $player->sendForm(new EconomyShopItemsForm(...$this->classInfo));
    }
}