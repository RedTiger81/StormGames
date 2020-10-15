<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 03 Mart 2020
 */
declare(strict_types=1);

namespace Eren5960\SkyBlock\forms;

use jojoe77777\FormAPI\ModalForm;
use pocketmine\player\Player;

class InfoForm extends ModalForm {
	/**
	 * Info constructor.
	 * @param string $title
	 * @param string $content
	 * @param string $one
	 * @param string $two
	 * @param callable|null $trueCallable
	 * @param callable|null $falseCallable
	 */
	public function __construct($title, $content, $one, $two, ?callable $trueCallable, ?callable $falseCallable){
		parent::__construct(function (Player $player, bool $response) use($trueCallable, $falseCallable){
			$callable = null;
			if($response && $trueCallable !== null){
				$callable = $trueCallable;
			}elseif($falseCallable !== null){
				$callable = $falseCallable;
			}
			if(!is_null($callable)) $callable($player);
		});
		$this->setTitle($title);
		$this->setContent($content);
		$this->setButton1($one);
		$this->setButton2($two);
	}
}