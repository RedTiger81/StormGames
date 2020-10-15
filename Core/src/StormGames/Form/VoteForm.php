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
use StormGames\Form\vote\VoteErrorForm;
use StormGames\Prefix;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\task\GiveVoteRewardTask;
use StormGames\SGCore\utils\IconUtils;

class VoteForm extends MenuForm{
	public static $queue = [];
	public const CLAIM = "l34SfOKz2b2aB2KW7K9ysjbfick2FG1HWR";
	public const VCS = [
		[
			"page" => 1,
			"check" => "http://minecraftpocket-servers.com/api-vrc/?object=votes&element=claim&key=" . self::CLAIM . "&username={USERNAME}",
			"claim" => "http://minecraftpocket-servers.com/api-vrc/?action=post&object=votes&element=claim&key=" . self::CLAIM . "&username={USERNAME}"
		]
	];

	/** @var string */
	public static $reward = '- 3 %crate.tier.vote';

	public function __construct(SGPlayer $player){
		$options = [];
		foreach(self::VCS as $key => $vcs){
			$options[] = new MenuOption(TextFormat::YELLOW . $player->translate('forms.vote.button', [$vcs["page"]]), IconUtils::get('vote'));
		}
		parent::__construct(sprintf(Prefix::FORM_TITLE, $player->translate('forms.vote.title')), TextFormat::GREEN . $player->translate("forms.vote.text", [TextFormat::LIGHT_PURPLE . $player->translateExtended(self::$reward) . TextFormat::AQUA]), $options);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		if(isset(self::$queue[$player->getId()])){
			$player->sendForm(new VoteErrorForm($player, TextFormat::RED . $player->translate("forms.vote.slowDown")));
		}else{
			$vcs = self::VCS[$selectedOption];
			foreach($vcs as $key => $value){
				$vcs[$key] = str_replace("{USERNAME}", $player->getName(), $value);
			}
			$player->getServer()->getAsyncPool()->submitTask(new GiveVoteRewardTask($player->getName(), $vcs));
		}
	}
}