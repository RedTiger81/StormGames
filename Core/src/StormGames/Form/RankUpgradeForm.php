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
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;
use StormGames\Rank\Rank;
use StormGames\Rank\RankManager;

class RankUpgradeForm extends MenuForm{
	public function __construct(SGPlayer $player, Rank $nextRank){
		$options = [
			new MenuOption($player->translate('rank.form.upgrade'))
		];
		$text = "\n" . TextFormat::RESET . TextFormat::AQUA . $player->translate('rank.form.next.rank', [$nextRank->getNameFor($player)]) . "\n\n";
		$options_ = $nextRank->getOptions();
		$statue = $nextRank->getPlayerStatue($player);
		foreach($options_ as $name => $option){
			$text .= TextFormat::RESET . TextFormat::AQUA . $player->translate('rank.form.next.' . $name, [TextFormat::WHITE . $option]) . " §7(Sendeki: §a" . $statue[$name] . "§7)\n\n";
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::DARK_GREEN . $player->translate('rank.form.title')), $text, $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player*/
		$rank = RankManager::getRank($player->rankId + 1);
		if($rank->canUpgrade($player)){
			$player->reduceMoney($rank->money);
			$player->reduceXp($rank->xp);
			$player->setRank($player->rankId + 1);
			$player->sendMessage(TextFormat::GREEN . $player->translate('rank.form.success', [$rank->getNameFor($player)]));
		}else{
			$player->sendMessage(TextFormat::RED . $player->translate('rank.form.error'));
		}
	}
}