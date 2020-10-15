<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\anvil;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Label;
use pocketmine\form\element\Slider;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\enchant\EnchantManager;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;

class AnvilEnchantBuyForm extends CustomForm{
	/** @var Enchantment */
	private $enchantment;

	public function __construct(SGPlayer $player, Enchantment $enchantment, int $startingLevel = 1){
		$this->enchantment = $enchantment;
		list($money, $xp) = EnchantManager::getPrice($enchantment->getId());
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.anvil.enchant')), [
			new Label('text', TextFormat::LIGHT_PURPLE . $player->translate('forms.anvil.enchant.buy.text', [
				TextFormat::GRAY . $player->translate($enchantment->getName()) . TextFormat::LIGHT_PURPLE,
				TextFormat::GRAY . $player->translate(str_replace('%', '', $enchantment->getName()) . '.desc') . TextFormat::LIGHT_PURPLE,
				sprintf(Utils::addMonetaryUnit($money, true), TextFormat::GRAY, '') . TextFormat::LIGHT_PURPLE,
				TextFormat::GRAY . $xp
			])),
			new Slider('level', $player->translate('forms.anvil.enchant.level'), $startingLevel, $enchantment->getMaxLevel())
		]);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		$level = (int) $data->getFloat('level');
		list($costMoney, $costXp) = EnchantManager::getPrice($this->enchantment->getId());
		$costMoney *= $level;
		$costXp *= $level;
		if($player->getMoney() >= $costMoney and $player->getXp() >= $costXp){
			$player->reduceMoney($costMoney);
			$player->reduceXp($costXp);

			$pk = new TextPacket();
			$pk->type = TextPacket::TYPE_RAW;
			$heldItem = $player->getInventory()->getItemInHand();
			$pk->needsTranslation = EnchantManager::add($player, $heldItem, new EnchantmentInstance($this->enchantment, $level));
			$player->getInventory()->setItemInHand($heldItem);
			$pk->message = Prefix::ANVIL() . $player->translate('forms.anvil.enchant.buy.success', [
				TextFormat::LIGHT_PURPLE . $player->translate(substr($this->enchantment->getName(), 1)) . TextFormat::GRAY
			]);
			$player->getNetworkSession()->sendDataPacket($pk);
		}else{
			$player->sendMessage(Prefix::ANVIL() . TextFormat::RED . $player->translate('forms.anvil.enchant.buy.cost'));
		}
	}
}