<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\utils\Utils;
use StormGames\SGCore\entity\utils\Skins;

//define("INVISIBLE_SKIN", str_repeat("\x00", 8192));

/* Bu entity default levelde olmak zorundadır.
 */
class MoneyStatue extends RDHuman{
	private const FORMAT = TextFormat::YELLOW . '%d. %s' . TextFormat::EOL . TextFormat::AQUA . '%s';

	/** @var int */
	private $queue = 1;
	/** @var int */
	private $money = 0;
	/** @var bool */
	private $updatedSkin = false;

	/** @var bool */
	public static $hasSkin = true;
	/** @var int */
	public static $minMoney = -1;
	/** @var MoneyStatue[] */
	public static $statues = [];

	public static function checkForUpdate(int $money){
		if(MoneyStatue::$minMoney < $money){
			foreach(self::$statues as $statue){
				if($statue->getMoney() < $money){
					$statue->update();
				}
			}
		}
	}

	public function __construct(World $level, CompoundTag $nbt){
		$this->skin = Skins::getSkin("knight");
		$this->setNameTagAlwaysVisible(true);
		parent::__construct($level, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		self::$statues[] = $this;
		$this->queue = $nbt->getInt('Queue', 1) - 1;
		$this->update();
	}

	public function getMoney() : int{
		return $this->money;
	}

	public function getUsername() : ?string{
		return TopMoneyFloatText::$list[$this->queue][0] ?? null;
	}

	public function update() : void{
		$result = TopMoneyFloatText::$list[$this->queue] ?? [null];
		if($result[0] !== null){
			$this->money = Utils::removeMonetaryUnit($result[1]);
			$this->setNameTag(sprintf(self::FORMAT, $this->queue + 1, $result[0], $result[1]));
			$this->updatedSkin = self::$hasSkin = false;
			$this->updateSkin($result);
		}
	}

	public function updateSkin(array $result = null) : void{
		$result = $result ?? TopMoneyFloatText::$list[$this->queue] ?? [null];
		if($result[0] !== null){
			$player = Server::getInstance()->getPlayerExact($result[0]);
			if($player !== null){
				$this->setSkin($player->getSkin());
				$this->updatedSkin = self::$hasSkin = true;
			}
		}
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->getMoney() < self::$minMoney){
			self::$minMoney = $this->getMoney();
		}

		if(!$this->updatedSkin){
			self::$hasSkin = false;
		}

		parent::entityBaseTick($tickDiff);

		return true;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt('Queue', $this->queue + 1);
		return $nbt;
	}

	public function hasMovementUpdate() : bool{ return false; }

	protected function updateMovement(bool $teleport = false) : void{}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}
}