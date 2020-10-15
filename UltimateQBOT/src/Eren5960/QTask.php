<?php

declare(strict_types=1);

namespace Eren5960;

use pocketmine\scheduler\Task;

class QTask extends Task{

	public function onRun(int $currentTick){
		$api = QBOT::getAPI();
		$question = $api->randQuestion();
		$api->setQuestion($question);
		$api->broadcastQuestion($question);
	}
}