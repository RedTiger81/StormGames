<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form\mission;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\MissionsForm;
use StormGames\SGCore\mission\Mission;
use StormGames\Prefix;

class MissionInfoForm extends MenuForm{
	/** @var string */
	private $class;
	/** @var int */
	private $type;

	public function __construct(SGPlayer $player, int $id){
		$this->class = Mission::getMission($id);
		if($player->isMissionCompleted($id)){
			$subText = TextFormat::GREEN . '%mission.subtext.completed';
		}else{
			if($player->getCurrentMission() !== null){
				if($player->getCurrentMission() instanceof $this->class){
					$this->type = 1;
					$subText = TextFormat::GREEN . '%mission.progress ' . $player->getCurrentMission()->getProgressText();
					$options = [
						new MenuOption(TextFormat::RED . $player->translate('mission.cancel'))
					];
				}else{
					$subText = TextFormat::RED . '%mission.subtext.haveMission';
				}
			}else{
				$this->type = 0;
				$options = [
					new MenuOption(TextFormat::DARK_GREEN . $player->translate('mission.start'))
				];
			}
		}
		parent::__construct(
			sprintf(Prefix::FORM_TITLE, TextFormat::YELLOW . $player->translate('mission')),
			TextFormat::YELLOW . $player->translate('mission.info', [
				TextFormat::GRAY . $this->class::getName($player) . TextFormat::YELLOW,
				TextFormat::GRAY . $this->class::getDescription($player) . TextFormat::YELLOW,
				TextFormat::GRAY . $this->class::getRewardText() . TextFormat::YELLOW
			]) . TextFormat::EOL . TextFormat::EOL . (isset($subText) ? $player->translateExtended($subText) : ''),
			$options ?? []
		);
	}

	public function onSubmit(Player $player, int $selectedOption) : void{
		/** @var SGPlayer $player */
		if($this->type === 0){ // start
			$player->setCurrentMission(new $this->class($player));
			$player->sendMessage(Prefix::MISSION() . TextFormat::GRAY . $player->translate('mission.start.success', [TextFormat::YELLOW . $this->class::getName($player) . TextFormat::GRAY]));
		}else{ // cancel
			$player->setCurrentMission(null);
			$player->sendMessage(Prefix::MISSION() . TextFormat::GRAY . $player->translate('mission.cancel.success', [TextFormat::RED . $this->class::getName($player) . TextFormat::GRAY]));
		}
	}

	public function onClose(Player $player) : void{
		/** @var SGPlayer $player */
		if($player->getCurrentMission() === null){
			$player->sendForm(new MissionsForm($player));
		}
	}
}