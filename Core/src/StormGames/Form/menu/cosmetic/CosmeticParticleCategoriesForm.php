<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu\cosmetic;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Form\menu\CosmeticForm;
use StormGames\Particle\Fun;
use StormGames\Particle\Hat;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class CosmeticParticleCategoriesForm extends MenuForm{

	public function __construct(SGPlayer $player){
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.shop.particle.title')), "", [
			new MenuOption($player->translate("forms.shop.particle.hats"), new FormIcon("textures/items/chainmail_helmet", FormIcon::IMAGE_TYPE_PATH)),
			new MenuOption($player->translate("forms.shop.particle.fun"), new FormIcon("textures/items/magma_cream", FormIcon::IMAGE_TYPE_PATH)),
			new MenuOption($player->translate("particles.remove"), new FormIcon("textures/ui/cancel", FormIcon::IMAGE_TYPE_PATH))
		]);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		static $class = [
			Hat::class,
			Fun::class
		];

		if(isset($class[$selectedOption])){
			$player->sendForm(new CosmeticParticlesForm($player, $this->getOption($selectedOption)->getText(), $class[$selectedOption]));
		}else{
			$player->getCosmetics()->setParticle(null);
			$player->sendMessage(Prefix::COSMETIC() . TextFormat::RED . $player->translate("forms.cosmetics.particle.remove"));
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new CosmeticForm($player));
	}
}