<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Form;

use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Slider;
use pocketmine\form\element\Toggle;
use pocketmine\form\element\Input;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\permission\GroupManager;
use StormGames\SGCore\SGPlayer;

class RemotePlayerForm extends CustomForm{

	/** @var SGPlayer */
	protected $target;

	public function __construct(SGPlayer $target){
		$this->target = $target;
		parent::__construct("Oyuncu Yönet", $this->getElements());
	}

	public function getElements() : array{
		return [
			new Input('money', "Para", "", (string) $this->target->getMoney()),
			new Input('coins', "Nakit", "", (string) $this->target->getCoins()),
			new Slider('vote', "Oy Kasa Anahtarı", 0.0, 50.0, 1.0, (float) $this->target->getCrateKeys("vote")),
			new Dropdown('group', "Grup", $groupKeys = GroupManager::getGroupsList(), array_search($this->target->getGroup()->getName(), $groupKeys, true)),
			new Dropdown('gm', "GameMode", ["Hayatta Kalma", "Yaratıcı", "Maceracı", "İzleyici"], $this->target->getGamemode()->getMagicNumber()),
			new Toggle('op', "Op", $this->target->isOp()),
		];
	}

	public function onSubmit(Player $player, CustomFormResponse $data) : void{
		$this->target->setMoney((int) $data->getString('money'), false);
		$this->target->setCoins((int) $data->getString('coins'), false);
		$this->target->setCrateKeys("vote", (int) $data->getFloat('vote'));
		/** @noinspection PhpUndefinedMethodInspection */
		$this->target->setGroup(GroupManager::getGroup($this->getElement(3)->getOption($data->getInt('group'))));
		$this->target->setGamemode(GameMode::fromMagicNumber($data->getInt('gm')));
		$this->target->setOp($data->getBool('op'));
		$this->target->updateNameTag();
		$this->target->updateAllDatabase();

		$player->sendMessage(Prefix::MAIN . TextFormat::GREEN . $this->target->getName() . " oyuncusu başarıyla güncellendi!");
	}

	public function onClose(Player $player) : void{
		$player->sendMessage(Prefix::MAIN . TextFormat::GRAY . $this->target->getName() . " oyuncusu güncellenmedi!");
	}
}