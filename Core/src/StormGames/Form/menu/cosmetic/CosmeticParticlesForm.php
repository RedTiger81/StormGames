<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu\cosmetic;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Particle\Particle;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class CosmeticParticlesForm extends MenuForm{

	/** @var MenuOption[] */
	private $buttons;
	/** @var string */
	private $class;

	public function __construct(SGPlayer $player, string $title, string $class){
		$this->class = $class;
		parent::__construct(sprintf(Prefix::FORM_TITLE, $title), '', $this->buttons = $this->getButtons($player, $class));
	}

	private function getButtons(SGPlayer $player, string $class) : array{
		$buttons = [];

		foreach(Particle::getParticles() as $particle){
			if($particle instanceof $class and $particle->canUse($player)){
				$buttons[$particle->getName()] = new MenuOption($particle->getTranslatedName($player->getLanguage()), $particle->getFormIcon());
			}
		}

		return $buttons;
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$particle = Particle::getParticle(array_keys($this->buttons)[$selectedOption]);

		if($particle !== null){
			$particleName = $player->getCosmetics()->getParticle();
			$particleName = $particleName !== null ? $particle->getName() : null;
			if($particleName === $particle->getName()){
				$player->sendMessage(Prefix::COSMETIC() . TextFormat::GRAY . $player->translate("forms.cosmetics.particle.already.use"));
			}else{
				$player->getCosmetics()->setParticle($particle);
				$player->sendMessage(Prefix::COSMETIC() . TextFormat::GREEN . $player->translate("forms.cosmetics.particle.use", [
					$particle->getTranslatedName($player->getLanguage()) . TextFormat::GREEN
				]));
			}
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		$player->sendForm(new CosmeticParticleCategoriesForm($player));
	}
}