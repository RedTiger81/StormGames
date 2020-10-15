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

class WarpCategoryForm extends MenuForm{

	/** @var string */
	private $category;

	public function __construct(SGPlayer $player, string $category){
		$this->category = $category;
		$options = [];
		foreach(WarpCommand::$warps[$category] as $warpName => $value){
			if(!in_array($warpName, WarpCommand::DENIED, true)){
				$options[] = new MenuOption(TextFormat::DARK_RED . $player->translate("warp.$category.$warpName") . (isset($value['alias']) ? (TextFormat::EOL . TextFormat::GRAY . '/w ' . (is_array($value['alias']) ? implode("/", $value['alias']) : $value['alias'])) : ''), isset($value['icon']) ? new FormIcon($value['icon']) : null);
			}
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, TextFormat::RED . $player->translate('warp.'.$category)), '', $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		$player->teleport(WarpCommand::$warps[$this->category][array_keys(WarpCommand::$warps[$this->category])[$selectedOption + 1]]['loc']);
	}
}