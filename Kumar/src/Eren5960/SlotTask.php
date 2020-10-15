<?php

declare(strict_types=1);

namespace Eren5960;

use Eren5960\SkyBlock\SkyPlayer;
use muqsit\invmenu\inventory\InvMenuInventory;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\Task;

class SlotTask extends Task{
	/** @var SkyPlayer */
	public $player;
	/** @var InvMenuInventory */
	public $inv;
	/** @var int */
	public $bet;
	/** @var array */
	public $contents = [];

	/**
	 * @param SkyPlayer  $player
	 * @param InvMenuInventory $inv
	 * @param int     $bet
	 * @param array   $contents
	 */
	public function __construct(SkyPlayer $player, InvMenuInventory $inv, int $bet, array $contents){
		$this->player = $player;
		$this->inv = $inv;
		$this->bet = $bet;
		$this->contents = $contents;
	}

	public $task = 0;
	public $count = 0;

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		if($currentTick % 20 === 0){
			$this->task++;
		}
		if($this->task <= 2){
			Slot::spinAll($this->inv);
			return;
		}

		if($this->task <= 5){
			Slot::spinSlot($this->inv, 2);
			if($currentTick % 7 === 0){
				Slot::spinSlot($this->inv, 1);
			}else{
				return;
			}
		}

		if($this->task <= 8){
			if($currentTick % 10 === 0){
				Slot::spinSlot($this->inv, 2);
			}
		}

		if($this->task === 10){
			$item1 = $this->inv->getItem(12);
			$item2 = $this->inv->getItem(13);
			$item3 = $this->inv->getItem(14);
			$player = $this->player;
			$player->removeCurrentWindow();
			if($item1->getId() === $item2->getId() && $item1->getId() === $item3->getId()){
				$bet = [
					ItemIds::DIAMOND => 3,
					ItemIds::EMERALD => 2,
					ItemIds::COAL => 1.5
				];
				$this->bet = intval(ceil($this->bet * $bet[$item1->getId()]));
				$player->addMoney($this->bet);
				$player->sendTitle("§aKazandın", '§e' . $this->bet . ' TP');
			}else{
				$player->sendTitle("§cKaybettin", '§e' . $this->bet . ' TP');
			}
		}
	}
}