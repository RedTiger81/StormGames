<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\menu;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Label;
use pocketmine\form\element\Input;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\Utils;

class ProfileForm extends CustomForm{

	/** @var string[] */
	private $langs;
	/** @var bool */
	private $changeable;
	/** @var bool */
	private $exit;

	public function __construct(SGPlayer $player, SGPlayer $target = null, bool $exit = false){
		if($target === null){
			$target = $player;
		}

		$this->langs = Language::getTranslatedLanguageNames();
		$this->changeable = $player->getId() === $target->getId();
		$this->exit = $exit;
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::AQUA . $player->translate('forms.menu.profile.title')), $this->getElements($player, $target));
	}

	public function getElements(SGPlayer $player, SGPlayer $target) : array{
		if($this->changeable){
			return [
				new Label('label', $player->translate("forms.menu.profile.label", [
					TextFormat::WHITE . $target->getName(),
					TextFormat::WHITE . $player->translate("groups." . $target->getGroup()->getName()),
					TextFormat::WHITE . $target->getCoins(),
					TextFormat::WHITE . date("d.m.Y H:i:s", (int) ($target->getFirstPlayed() / 1000)),
					TextFormat::WHITE . date("d.m.Y H:i:s", (int) ($target->getLastPlayed() / 1000)),
					TextFormat::WHITE . $this->getTimePlayedNow($player, $target)
				])),
				new Dropdown('lang', $player->translate("forms.menu.profile.language"), $this->langs, array_search($target->translate("language.name"), $this->langs)),
				new Input('bio', $player->translate("forms.menu.profile.biography"), "", $target->getBiography())
			];
		}else{
			return [
				new Label('label', $player->translate("forms.menu.profile.label", [
					TextFormat::WHITE . $target->getName(),
					TextFormat::WHITE . $player->translate("groups." . ($target->getGroup() !== null ? $target->getGroup()->getName() : "player")),
					TextFormat::WHITE . $target->getCoins(),
					TextFormat::WHITE . date("d.m.Y H:i:s", (int) ($target->getFirstPlayed() / 1000)),
					TextFormat::WHITE . date("d.m.Y H:i:s", (int) ($target->getLastPlayed() / 1000)),
					TextFormat::WHITE . $this->getTimePlayedNow($player, $target)
				])),
				new Label('label2', $player->translate("forms.menu.profile.label2", [
					TextFormat::WHITE . $target->translate("language.name"),
					TextFormat::WHITE . $target->getBiography()
				]))
			];
		}
	}

	public function getTimePlayedNow(SGPlayer $player, SGPlayer $target) : string{
		$interval = Utils::secondsToDateInterval($target->getTimePlayedNow());

		return $interval->format($player->translateExtended("%a !date.days %h !date.hours %i !date.minutes %s !date.seconds", [], "!"));
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		/** @var SGPlayer $player */
		if($this->changeable){
			/** @noinspection PhpUndefinedMethodInspection */
			$language = $this->getElement(1)->getOption($data->getInt('lang'));
			$bio = $data->getString('bio');

			$player->setLanguage(Language::getLanguages()[array_search($language, $this->langs, true)]);
			$player->setBiography($bio);
		}
	}

	public function onClose(Player $player) : void{
		if(!$this->exit){
			$class = SGCore::$formClasses["menu"];
			$player->sendForm(new $class($player));
		}
	}
}