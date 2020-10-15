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
 * @date 21 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock;
 
use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\island\IslandManager;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\nbt\tag\CompoundTag;
use StormGames\SGCore\SGPlayer;

class SkyPlayer extends SGPlayer{
	public function throwJump(): void{
		$new = $this->getDirectionVector();
		$new->y = 1.46714060357822;
		$this->setMotion($new->multiply(1.5));
	}

	public function getSkinTag(): CompoundTag{
		return CompoundTag::create()->setString("Name", $this->skin->getSkinId())
			->setByteArray("Data", $this->skin->getSkinData())
			->setByteArray("CapeData", $this->skin->getCapeData())
			->setString("GeometryName", $this->skin->getGeometryName())
			->setByteArray("GeometryData", $this->skin->getGeometryData());
	}

	public function sendAlert(string $title, string $text, string $button1, string $button2, callable $yes = null, callable $no = null) : void{
		$form = new ModalForm(function($player, $data = null) use ($yes, $no){
			if($data){
				if($yes !== null) $yes();
			}else{
				if($no !== null) $no();
			}
		});
		$form->setTitle($title);
		$form->setContent($text);
		$form->setButton1($button1);
		$form->setButton2($button2);
		$form->sendToPlayer($this);
	}

	public function sendAlertCustom(string $title, string $text) : void{
		$form = new CustomForm(null);
		$form->setTitle($title);
		$form->addLabel($text);
		$form->sendToPlayer($this);
	}

	public function isInLobby(): bool{
		return $this->getWorld()->getId() === $this->getServer()->getWorldManager()->getDefaultWorld()->getId();
	}

	public function isInArena(): bool{
		return $this->getWorld()->getFolderName() === 'arena';
	}

	public function isInIsland(): bool{
		return strstr($this->getWorld()->getFolderName(), '-') !== false;
	}

	public function getNowIsland(): ?IslandBase{
		$name = explode('-', $this->getWorld()->getFolderName())[0];
		return IslandManager::getIslandByName($name);
	}

	public function handleChange(string $pass): bool{
		$island = IslandManager::getIslandByName($this->getName());
		if($island === null){
			return false;
		}

		return $island->handleChange($this, $pass);
	}

	public function getSpawnerTime(): int{
		static $array = [
			"mvp+" => 180, "mvp" => 300,
			"vip+" => 360, "vip" => 400,
			"owner" => 10
		];
		return $array[$this->getGroup()->getName()] ?? 500;
	}

	public function getMaxCSCont(): int{
		static $array = [
			"mvp+" => 10, "mvp" => 7,
			"vip+" => 5, "vip" => 3,
			"owner" => 1000
		];
		return $array[$this->getGroup()->getName()] ?? 1;
	}
}