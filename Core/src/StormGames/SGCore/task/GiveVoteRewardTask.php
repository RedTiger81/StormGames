<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Internet;
use StormGames\Form\vote\VoteErrorForm;
use StormGames\Form\vote\VoteSuccessForm;
use StormGames\Form\VoteForm;
use StormGames\SGCore\SGPlayer;

class GiveVoteRewardTask extends AsyncTask{

	public const STATUS_VOTED = 0;
	public const STATUS_CLAIMED = 1;
	public const STATUS_NOT_VOTED = 2;
	public const STATUS_ERROR_VOTE = 3;

	/** @var string */
	protected $name;
	/** @var string[] */
	protected $vcs;
	/** @var int */
	private $status = self::STATUS_ERROR_VOTE;

	public function __construct(string $name, array $vcs){
		$this->name = $name;
		$this->vcs = $vcs;

		VoteForm::$queue[$this->name] = true;
	}

	public function onRun() : void{
		$check = Internet::getURL($this->vcs["check"]);
		if($check !== false){
			$json = json_decode($check, true);
			$voted = $json["voted"] ?? false;
			$claimed = $json["claimed"] ?? false;
			if($voted){
				if($claimed){
					$this->status = self::STATUS_CLAIMED;
				}elseif(($check = Internet::getURL($this->vcs["claim"])) !== false){
					$json = json_decode($check, true);
					$voted = $json["voted"] ?? false;
					$claimed = $json["claimed"] ?? false;
					$this->status = ($voted and $claimed) ? self::STATUS_VOTED : self::STATUS_ERROR_VOTE;
				}
			}else{
				$this->status = self::STATUS_NOT_VOTED;
			}
		}
	}

	public function onCompletion() : void{
		$server = Server::getInstance();
		/** @var SGPlayer $player */
		$player = $server->getPlayerExact($this->name);

		if($player !== null){
			switch($this->status){
				case self::STATUS_VOTED:
					$player->giveVoteReward();
					$player->sendForm(new VoteSuccessForm($player, TextFormat::GREEN . $player->translate("forms.vote.voted")));
					break;
				case self::STATUS_CLAIMED:
					$player->sendForm(new VoteErrorForm($player, TextFormat::RED . $player->translate("forms.vote.claimed")));
					break;
				case self::STATUS_NOT_VOTED:
					$player->sendForm(new VoteErrorForm($player, TextFormat::RED . $player->translate("forms.vote.notVoted", [TextFormat::YELLOW . "https://www.stormgames.net/vote/{$this->vcs["page"]}" . TextFormat::RED])));
					break;
				case self::STATUS_ERROR_VOTE:
					$player->sendForm(new VoteErrorForm($player, TextFormat::RED . $player->translate("forms.vote.error")));
					break;
			}
		}

		unset(VoteForm::$queue[$this->name]);
	}

	public function setGarbage(){}
}