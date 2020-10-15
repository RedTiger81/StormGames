<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Particle;

use pocketmine\form\FormIcon;
use pocketmine\world\Position;
use pocketmine\player\Player;
use StormGames\Particle\fun\EagleWing;
use StormGames\Particle\fun\MarkParticle;
use StormGames\Particle\fun\WaterSphere;
use StormGames\Particle\hat\AngelCrown;
use StormGames\Particle\hat\ChristmasHat;
use StormGames\Particle\hat\HeartCrown;
use StormGames\SGCore\lang\Language;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\TextUtils;

abstract class Particle{

	/** @var array */
	protected static $particles = [];
	/** @var Position */
	protected $pos;
	/** @var string */
	private $name;

	public static function init() : void{
		self::registerParticle(new ChristmasHat());
		self::registerParticle(new AngelCrown());
		self::registerParticle(new EagleWing());
		self::registerParticle(new MarkParticle());
		self::registerParticle(new HeartCrown());
		self::registerParticle(new WaterSphere());
	}

	public function __construct(){
		$this->name = TextUtils::classStringToName(static::class);
		$this->pos = new Position();
	}

	public static function registerParticle(Particle $particle){
		self::$particles[$particle->getName()] = $particle;
	}

	public static function getParticle(string $name) : ?Particle{
		return self::$particles[$name] ?? null;
	}

	public static function getParticleWithClass(string $name) : ?Particle{
		return self::$particles[TextUtils::classStringToName($name)] ?? null;
	}

	/**
	 * @return Particle[]
	 */
	public static function getParticles() : array{
		return self::$particles;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getTickRate() : int{
		return 10;
	}

	public function onClose() : void{

	}

	public function canUse(SGPlayer $player) : bool{
		return $player->hasPermission(DefaultPermissions::VIP_PLUS);
	}

	public function getFormIcon() : ?FormIcon{
		return null; // TODO
	}

	abstract public function createParticle(Player $player, int $currentTick) : void;

	abstract public function getTranslatedName(string $locale = Language::DEFAULT_LANGUAGE) : string;
}