<?php
/*
 *  _____               _               ___   ___  __ 
 * /__   \___  _ __ ___| |__   /\/\    / __\ / _ \/__\
 *   / /\/ _ \| '__/ __| '_ \ /    \  / /   / /_)/_\  
 *  / / | (_) | | | (__| | | / /\/\ \/ /___/ ___//__  
 *  \/   \___/|_|  \___|_| |_\/    \/\____/\/   \__/
 *
 * (C) Copyright 2019 TorchMCPE (http://torchmcpe.fun/) and others.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Contributors:
 * - Eren Ahmet Akyol
 */
declare(strict_types=1);

namespace StormGames\SGCore\boss;

use pocketmine\bossbar\BossBar;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\utils\ExperienceUtils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\player\Player;
use pocketmine\Server;
use StormGames\SGCore\SGPlayer;

class BossEntity extends Living{
	public const MAX_LEVEL = 2;

	public const HEAL_TICK = 25;

	public $attackMax = 10;

	public const NETWORK_ID = EntityLegacyIds::IRON_GOLEM;

	public static $level = 0;

	public static $heals = [
		1000,
		2500,
		3000
	];

	public static $damages = [
		5.5,
		6,
		6.5
	];

	public static $knock = [
		0.7,
		1.0,
		1.3
	];

	/** @var float|int */
	protected $customGravity;
	/** @var int */
	protected $jumpTicks = 0;
	protected $attackDelay = 0;
	public $height = 1.7;
	public $width = 0.7; // 0.7 iyidi ama deneyelim bakalım
	/** @var Bossbar */
	public $bar;
	public $lastDamageTick = PHP_INT_MAX;

	/** @var int[] */
	public $attacks = [];

	public function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setScale(2);

		$this->speed += ((self::$level + 2) / 10);

		$this->customGravity = -$this->gravity * 4;
		$this->jumpVelocity = $this->gravity * 15;

		$this->setMaxHealth(self::$heals[self::$level]);
		$this->setHealth(self::$heals[self::$level]);

		$this->setCanSaveWithChunk(false);
		$this->bar = new BossBar("§c§k.,,§r " . $this->getNameV2() . " §8| §6Seviye: §c" . (self::$level + 1) . " §c§k.,.", 1);
	}

	protected $speed = 0.7;

	protected function getSpeed() : float{
		return $this->speed;
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		$this->lastDamageTick = time();

		if($source instanceof EntityDamageByEntityEvent){
			$player = $source->getDamager();
			if($player instanceof Player){
				$id = $player->getName();
				if(!isset($this->attacks[$id])){
					$this->attacks[$id] = 0;
				}
				$this->attacks[$id]++;
			}
		}
	}

	public function follow(Entity $target) : void{
		$x = $target->location->x - $this->location->x;
		$y = $target->location->y - $this->location->y;
		$z = $target->location->z - $this->location->z;
		$xz_modulus = sqrt($xz_sq = $x * $x + $z * $z);
		if($xz_sq < 1.2){
			$this->motion->x = $this->motion->z = 0;
		}else{
			$speed_factor = $this->getSpeed() * 0.15;
			$this->motion->x = $speed_factor * ($x / $xz_modulus);
			$this->motion->z = $speed_factor * ($z / $xz_modulus);
		}
		$this->location->yaw = rad2deg(atan2(-$x, $z));
		$this->location->pitch = rad2deg(-atan2($y, $xz_modulus));
		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		if($this->isCollidedHorizontally){
			$this->teleport($target->getPosition());
		}
		$this->attackEntity($target);
	}

	public function attackEntity(Entity $player){
		if($this->attackDelay > $this->attackMax && $this->location->distanceSquared($player->getPosition()) < 12){
			$this->attackDelay = 0;
			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, self::$damages[self::$level], [], self::$knock[self::$level]);
			$player->attack($ev);
		}
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->closed){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0){
			return true;
		}
		$this->lastUpdate = $currentTick;

		if(!$this->isAlive()){
			if($this->onDeathUpdate($tickDiff)){
				$this->flagForDespawn();
			}

			return true;
		}

		$this->entityBaseTick($tickDiff);

		if($this->lastDamageTick + self::HEAL_TICK <= time()){
			$fark = $this->getMaxHealth() - $this->getHealth();
			if($fark !== 0){
				$this->setHealth($fark <= 10 ? $this->getMaxHealth() : ($this->getHealth() + ($fark / 10)));
				$this->lastDamageTick = time() - self::HEAL_TICK + 1;
			}else{
				$this->lastDamageTick = PHP_INT_MAX;
			}
		}

		if($this->isOnFire()){
			$this->extinguish();
		}

		if($this->jumpTicks > 0){
			--$this->jumpTicks;
		}

		if($this->attackDelay < $this->attackMax + 1){
			++$this->attackDelay;
		}

		if(!$this->onGround){
			if($this->motion->y > $this->customGravity){
				$this->motion->y = $this->customGravity;
			}else{
				$this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
			}
		}else{
			if($this->isCollidedHorizontally && $this->jumpTicks === 0){
				$this->jump();
			}else{
				$this->motion->y -= $this->gravity;
			}
		}
		if(($entity = $this->getWorld()->getNearestEntity($this->location, 32, Player::class, false)) !== null){
			$this->follow($entity);
		}


		$this->updateMovement();
		$this->motion->setComponents(0, 0, 0);
		$this->updateBar();
		return true;
	}

	public function jump() : void{
		parent::jump();
		$this->jumpTicks = 5;
	}

	public function getName() : string{
		return "boss";
	}

	public function updateBar() : void{
		$this->bar->setHealthPercent($this->getHealth() / self::$heals[self::$level]);
	}

	public function spawnTo(Player $player) : void{
		$this->bar->addViewer($player);
		parent::spawnTo($player);
	}

	public function despawnFrom(Player $player, bool $send = true) : void{
		$this->bar->removeViewer($player);
		parent::despawnFrom($player, $send);
	}

	public function onDeath() : void{
		arsort($this->attacks);
		self::$level++;
		$player = null;
		BossManager::$lastBoss = null;

		Server::getInstance()->broadcastMessage("§dBoss §7» §4Bidahaki gelişim daha güçlü olacak!");
		foreach($this->attacks as $name => $attack){
			if(($player_ = Server::getInstance()->getPlayerExact($name)) !== null){
				$player = $player_;
				break;
			}
		}
		if($player === null) return;

		if($player instanceof SGPlayer){
			$money = self::$level * 5000;
			$xp = self::$level * 50;
			Server::getInstance()->broadcastMessage("§dBoss §7» §cSeni mahvedicem §e" . $player->getName());
			$xp_ = "";
			if(mt_rand(0, self::MAX_LEVEL + 1) > 0){
				$xp_ = " §ave §c1 level";
				$player->addLevelXP(ExperienceUtils::getXpToCompleteLevel($player->getCurrentLevel()));
			}
			$player->sendMessage('§aTebrikler! ' . $this->getNameV2() . ' öldürerek §e' . $money . '$ §a, §d' . $xp . ' XP' . $xp_ . ' §akazandın.');
			$player->addMoney($money);
			$player->addExp($xp);
		}
		if(self::$level > self::MAX_LEVEL){
			self::$level = 0;
		}
	}

	public function getNameV2(): string{
		return "§bAcımasız Golem";
	}
}
