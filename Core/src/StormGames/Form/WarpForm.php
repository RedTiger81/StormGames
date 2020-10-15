<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\commands\WarpCommand;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;

class WarpForm extends MenuForm{

	public function __construct(SGPlayer $player){
		$options = [];
		foreach(WarpCommand::$warps as $worC => $value){
			$options[] = new MenuOption(TextFormat::DARK_RED . $player->translate('warp.' . $worC) . (isset($value['alias']) ? (TextFormat::EOL . TextFormat::GRAY . '/w ' . (is_array($value['alias']) ? implode("/", $value['alias']) : $value['alias'])) : ''), isset($value['icon']) ? new FormIcon($value['icon']) : null);
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::DARK_AQUA . $player->translate('warp')), '', $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		$key = array_keys(WarpCommand::$warps)[$selectedOption];
		if(WarpCommand::$warps[$key]['type'] === WarpCommand::WARP){
			$player->teleport(WarpCommand::$warps[$key]['loc']);
		}else{
			$player->sendForm(new WarpCategoryForm($player, $key));
		}
	}
}