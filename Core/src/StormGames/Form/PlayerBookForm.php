<?php
/*
 *  _____               _               ___   ___  __ 
 * /__   \___  _ __ ___| |__   /\/\    / __\ / _ \/__\
 *   / /\/ _ \| '__/ __| '_ \ /    \  / /   / /_)/_\  
 *  / / | (_) | | | (__| | | / /\/\ \/ /___/ ___//__  
 *  \/   \___/|_|  \___|_| |_\/    \/\____/\/   \__/
 *
 * (C) Copyright 2019 TorchMCPE (http://torchmcpe.fun/) and others.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Contributors:
 * - Eren Ahmet Akyol
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Helper\{FirstBook, GetMoney, LevelBook};
use StormGames\Helper\HelperManager;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;

class PlayerBookForm extends MenuForm{
	public $soon = 1;
	public function __construct(SGPlayer $player){
		$options = [
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.book.first')),
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.book.getMoney')),
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.book.level')),
			new MenuOption(TextFormat::DARK_GREEN . $player->translate('forms.book.soon'))
		];
		$this->soon = count($options) - 1;
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::GREEN . $player->translate('forms.book')), TextFormat::LIGHT_PURPLE . $player->translate("forms.book.select"), $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		if($selectedOption == $this->soon) return;
		/** @var SGPlayer $player */
		$data = $this->getData($selectedOption);
		HelperManager::add($player, $data[0], explode('.', $data[1])[2]);
		$player->sendMessage($player->translate("forms.book.selected", [$player->translate($data[1])]));
	}

	public function getData(int $selectedOption): array {
		$data = [
			FirstBook::class => "forms.book.first",
			GetMoney::class => "forms.book.getMoney",
			LevelBook::class => "forms.book.level"
		];
		return [array_keys($data)[$selectedOption], array_values($data)[$selectedOption]];
	}
}