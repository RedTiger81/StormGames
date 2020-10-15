<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\home\HomeCreateForm;
use StormGames\Form\home\HomeDeleteForm;
use StormGames\SGCore\manager\HomeManager;
use StormGames\Prefix;
use StormGames\SGCore\utils\IconUtils;

class HomeForm extends MenuForm{
	/** @var array */
	private $option;

	public function __construct(SGPlayer $player){
		$homes = HomeManager::getHomes($player->getLowerCaseName());
		$countHomes = count($homes);
		$homeIcon = IconUtils::get('home/add');
		if(HomeManager::canCreateHome($player, $countHomes)){
			$this->option[-1] = new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.home.create'), $homeIcon);
		}
		if($countHomes !== 0){
			$this->option[1] = new MenuOption(TextFormat::DARK_RED . $player->translate('forms.home.delete'), IconUtils::get('home/delete'));
		}

		foreach($homes as $name => $loc){
			$this->option[$name] = new MenuOption(TextFormat::DARK_PURPLE . $name, $homeIcon);
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.home')), isset($this->option[-1]) ? '' : TextFormat::GOLD . $player->translate('forms.home.create.limit'), $this->option);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$key = array_keys($this->option)[$selectedOption];
		if($key === -1){
			$player->sendForm(new HomeCreateForm($player));
		}elseif($key === 1){
			$player->sendForm(new HomeDeleteForm($player));
		}else{
			HomeManager::teleportHome($player, (string) $key);
		}
	}
}