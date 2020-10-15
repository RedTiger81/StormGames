<?php

declare(strict_types=1);

namespace Eren5960\handler;

abstract class BaseQuestion{
	/** @var string[] */
	private static $questions = [];

	public static function init(): void{
		self::register(MathQuestion::class);
		self::register(LiberalQuestion::class);
		self::register(HistoryQuestion::class);
		self::register(ShuffleCharQuestion::class);
	}

	/**
	 * @param string $question
	 */
	public static function register(string $question){
		self::$questions[] = $question;
	}

	/**
	 * @return array
	 */
	public static function getQuestions(): array{
		return self::$questions;
	}

	/**
	 * Question type
	 * @return string
	 */
	abstract public function getType(): string;

	/**
	 * Prepares the variables (answer and question)
	 */
	abstract public function prepare(): void;

	/** @var null|mixed */
	protected $answer = null;
	/** @var null|mixed */
	protected $question = null;

	/**
	 * @return mixed|null
	 */
	public function getAnswer(){
		return $this->answer;
	}

	/**
	 * @return mixed|null
	 */
	public function getQuestion(){
		return $this->question;
	}
}