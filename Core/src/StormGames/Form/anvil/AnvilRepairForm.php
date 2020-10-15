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
use pocketmine\form\element\StepSlider;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;
use StormGames\Prefix;

class AnvilRepairForm extends CustomForm{

	/** @var bool */
	private $canRepair;
	/** @var int[] */
	private $costs;

	public function __construct(SGPlayer $player){
		$heldItem = $player->getInventory()->getItemInHand();
		if($heldItem instanceof Durable and $heldItem->getDamage() > 0){
			$this->canRepair = true;
			$this->costs = [
				'full' => $heldItem->getDamage(),
				'half' => (int) floor($heldItem->getDamage() / 2)
			];
			$elements = [
				new Label('label',TextFormat::RED . $player->translate('forms.anvil.repair.text', [
					TextFormat::GRAY . $heldItem->getName() . TextFormat::RED,
					TextFormat::GRAY . Utils::addMonetaryUnit($this->costs['full']) . TextFormat::RED,
					TextFormat::GRAY . Utils::addMonetaryUnit($this->costs['half']) . TextFormat::RED
				])),
				new StepSlider('fullHalf', $player->translate('forms.anvil.repair.rate'), [
					$player->translate('forms.anvil.repair.rate.full'),
					$player->translate('forms.anvil.repair.rate.half')
				])
			];
		}else{
			$this->canRepair = false;
			$elements = [
				new Label('label',TextFormat::DARK_RED . $player->translate('forms.anvil.repair.error'))
			];
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::DARK_AQUA . $player->translate('forms.anvil.repair')), $elements);
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		if($this->canRepair){
			/** @var SGPlayer $player */
			$fullHalf = $data->getInt('fullHalf') === 0 ? 'full' : 'half';
			if($player->reduceMoney($this->costs[$fullHalf])){
				/** @var Durable $item */
				$item = $player->getInventory()->getItemInHand();
				if($fullHalf === 'full'){
					$item->setDamage(0);
				}else{
					$item->setDamage(intval(floor($item->getDamage() / 2)));
				}
				$player->getInventory()->setItemInHand($item);
				$player->sendMessage(Prefix::ANVIL() . $player->translate('forms.anvil.repair.success'));
			}else{
				$player->sendMessage(Prefix::ANVIL() . TextFormat::RED . $player->translate('error.generic.noMoney'));
			}
		}
	}
}