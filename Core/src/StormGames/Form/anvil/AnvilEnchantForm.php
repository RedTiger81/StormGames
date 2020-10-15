<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\anvil;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;
use StormGames\SGCore\utils\EnchantmentUtils;

class AnvilEnchantForm extends MenuForm{
	/** @var Enchantment[] */
	private $enchants;

	public function __construct(SGPlayer $player){
		$item = $player->getInventory()->getItemInHand();
		if(!empty($this->enchants = EnchantmentUtils::availableEnchantments($item))){
			$text = TextFormat::GRAY . $player->translate('forms.anvil.enchant.text', [TextFormat::GREEN . $item->getName() . TextFormat::RESET . TextFormat::GRAY]);
			$options = array_map(function(Enchantment $enchantment) use($player) : MenuOption{
				return new MenuOption($player->translate($enchantment->getName()), EnchantmentUtils::getIconByRarity($enchantment->getRarity()));
			}, $this->enchants);
		}else{
			$options = [];
			$text = TextFormat::RED . $player->translate('forms.anvil.enchant.error');
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.anvil.enchant')), $text, $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$enchant = $this->enchants[$selectedOption];
		$item = $player->getInventory()->getItemInHand();
		if(($ench = $item->getEnchantment($enchant)) !== null){
			if($ench->getLevel() >= $enchant->getMaxLevel()){
				$player->sendMessage(Prefix::ANVIL() . TextFormat::RED . $player->translate('forms.anvil.enchant.buy.error'));
			}else{
				$player->sendForm(new AnvilEnchantBuyForm($player, clone $enchant, $ench->getLevel() + 1));
			}
		}else{
			$player->sendForm(new AnvilEnchantBuyForm($player, clone $enchant));
		}
	}
}