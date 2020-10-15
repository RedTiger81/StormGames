<?php

declare(strict_types=1);

namespace Eren5960;

use Eren5960\handler\BaseQuestion;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;

class QBOT extends PluginBase{
	/** @var int */
	public const DELAY = 300; // 300 saniye(5 dakika) aralıkla
	/** @var null|QBOT */
	private static $api = null;
	/** @var null|BaseQuestion */
	private $current_question = null;
	/** @var int */
	public static $last_question = 0;

	/**
	 * @return QBOT|null
	 */
	public static function getAPI(): ?QBOT{
		return self::$api;
	}

	public function onLoad(): void{
		self::$api = $this;
		$this->getLogger()->info("§7» §6Ultimate QBot Author: §dfacebook.com/eren5960");
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new AnswerListener(), $this);
		BaseQuestion::init();
		$this->nextQuestion();
		$this->getLogger()->info("§7» §b" . count(BaseQuestion::getQuestions()) . " adet soru türü aktifleştirildi!");
	}

	/**
	 * @param int $interval
	 *
	 * @return TaskHandler
	 */
	public function nextQuestion(int $interval = self::DELAY): TaskHandler{
		self::$last_question = time() + self::DELAY + $interval;
		return $this->getScheduler()->scheduleDelayedTask(new QTask(), $interval * 20);
	}

	/**
	 * @param BaseQuestion|null $question
	 */
	public function setQuestion(BaseQuestion $question = null): void{
		$this->current_question = $question;
	}

	/**
	 * @return BaseQuestion|null
	 */
	public function getQuestion(): ?BaseQuestion{
		return $this->current_question;
	}

	/**
	 * @return BaseQuestion
	 */
	public function randQuestion(): BaseQuestion{
		$rand_q = BaseQuestion::getQuestions()[array_rand(BaseQuestion::getQuestions())];
		/** @var BaseQuestion $q */
		$q = new $rand_q;
		$q->prepare();
		return $q;
	}

	/**
	 * @param BaseQuestion $question
	 */
	public function broadcastQuestion(BaseQuestion $question): void{
		$bx = self::broadcaster();
		$bx(" ");
		$bx(" §7******** §3SORU BOTU §7******** ");
		$bx("§7» §7" . $question->getType());
		$bx("§7» §b" . $question->getQuestion());
		$bx("§7» §aDoğru cevabı ilk yazan kazanır!");
		$bx(" §7******** §3SORU BOTU §7******** ");
		$bx(" ");
	}

	/**
	 * @return callable
	 */
	public static function broadcaster(): callable{
		return function(string $message): void{
			Server::getInstance()->broadcastMessage($message);
		};
	}
}