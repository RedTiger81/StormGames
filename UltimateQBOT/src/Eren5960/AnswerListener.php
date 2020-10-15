<?php

declare(strict_types=1);

namespace Eren5960;

use Eren5960\handler\BaseQuestion;
use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class AnswerListener implements Listener{
	public static function onPlayerChat(PlayerChatEvent $event){
		$api = QBOT::getAPI();
		if($api->getQuestion() instanceof BaseQuestion){
			$answer = $api->getQuestion()->getAnswer();
			$message = $event->getMessage();
			self::reformat($message);

			if(strtolower($message) === strtolower(strval($answer))){
				$api->setQuestion();
				self::rewardPlayer($event->getPlayer(), strval($answer));
				$api->nextQuestion();
				return;
			}
			if($api::$last_question < time()){
				$bx = QBOT::broadcaster();
				$bx("§7» §bÖnceki soruyu bilen kimse olmadı, yeni soru geliyor!");
				$api->nextQuestion(10);
			}
		}
	}

	/**
	 * @param string $message
	 */
	public static function reformat(string &$message): void{
		if(in_array(substr($message, 0, 1), ['!', '*', '#'])){
			$message = substr($message, 1);
		}
	}

	/**
	 * @param SkyPlayer $player
	 * @param string  $answer
	 */
	public static function rewardPlayer(SkyPlayer $player, string $answer): void{
		$bx = QBOT::broadcaster();
		$money = rand(1000, 3500);
		$bx(" ");
		$bx(" §7******** §aSORU BOTU §7******** ");
		$bx("§7* §7Doğru cevabı §b" . $player->getName() . " §7vererek §b" . $money . "§f$ §7kazandı.");
		$bx("§7* Cevap §a" . $answer . " §7olacaktı.");
		$bx(" §7******** §aSORU BOTU §7******** ");
		$bx(" ");
		$player->addMoney($money);
	}
}