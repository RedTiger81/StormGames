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
 * @date 31 Mart 2020
 */
declare(strict_types=1);
 
namespace eren5960\OtoCS;
 
use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\sound\EnderChestCloseSound;
use pocketmine\world\sound\EnderChestOpenSound;
use pocketmine\world\sound\Sound;
use StormGames\Form\economy\EconomySellForm;

class CSInventory extends ChestInventory{
	/** @var string */
	public $player = null;

	public function __construct(Position $position){
		parent::__construct($position);
	}

	public function setHolderPosition(Position $pos) : void{
		$this->holder = $pos->asPosition();
	}

	protected function getOpenSound() : Sound{
		return new EnderChestOpenSound();
	}

	protected function getCloseSound() : Sound{
		return new EnderChestCloseSound();
	}

	public function canAddItem(Item $item) : bool{
		if($this->player === null) return parent::canAddItem($item);
		return $this->getPlayer() !== null && isset(EconomySellForm::ITEMS[$item->getId()]);
	}

	public function addItem(Item ...$slots) : array{
		if($this->player === null) return parent::addItem(...$slots);

		$player = $this->getPlayer();
		if($player instanceof SkyPlayer && $player->isOnline()){
			$money = 0;
			foreach($slots as $slot => $item){
				$money += EconomySellForm::ITEMS[$item->getId()] * $item->getCount();
			}
			$player->addMoney($money, false);
		}
		return [];
	}

	public function __debugInfo(){
		return ['id' => spl_object_id($this), 'hash' => spl_object_hash($this), 'player' => $this->player];
	}

	public function getPlayer(): ?SkyPlayer{
		return Server::getInstance()->getPlayerExact($this->player ?? "a");
	}
}