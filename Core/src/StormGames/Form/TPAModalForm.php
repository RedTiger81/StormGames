<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\ModalForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\Prefix;

class TPAModalForm extends ModalForm{

	/** @var SGPlayer */
	private $sender;

	public function __construct(SGPlayer $target, SGPlayer $sender){
		$this->sender = $sender;
		parent::__construct(
		    sprintf(Prefix::FORM_TITLE, TextFormat::GOLD . $target->translate('forms.tpa')),
            TextFormat::WHITE . $target->translate('forms.tpa.text.modal', [TextFormat::GOLD . $sender->getName() . TextFormat::WHITE]),
            TextFormat::DARK_GREEN . $target->translate('forms.accept'),
            TextFormat::DARK_RED . $target->translate('forms.deny')
        );
	}

	public function onSubmit(Player $player, bool $choice) : void{
		if($choice){
			$this->sender->teleport($player->getPosition());
			$this->sender->sendMessage(Prefix::TPA() . $this->sender->translate('forms.tpa.accept', [TextFormat::GREEN . $player->getName() . TextFormat::GRAY]));
		}else{
			$this->sender->sendMessage(Prefix::TPA() . $this->sender->translate('forms.tpa.deny', [TextFormat::RED . $player->getName() . TextFormat::GRAY]));
		}
	}
}