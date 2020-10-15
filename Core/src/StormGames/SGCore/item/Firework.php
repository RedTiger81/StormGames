<?php

/*
 *    ______              ______           _
 *    | ___ \             |  _  \         | |
 *    | |_/ /___  ___  ___| | | |_   _ ___| |_
 *    |    // _ \/ __|/ _ \ | | | | | / __| __|
 *    | |\ \ (_) \__ \  __/ |/ /| |_| \__ \ |_
 *    \_| \_\___/|___/\___|___/  \__,_|___/\__|
 *
 *
 *  Copyright (C) RoseDust, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Enes Yıldırım <enes5519@gmail.com>, July 2019
 */

declare(strict_types=1);

namespace StormGames\SGCore\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\world\sound\BlazeShootSound;
use StormGames\SGCore\entity\FireworkRocket;

class Firework extends Item{
	/** @var float */
	public const BOOST_POWER = 1.25;
	public const TYPE_SMALL_SPHERE = 0;
	public const TYPE_HUGE_SPHERE = 1;
	public const TYPE_STAR = 2;
	public const TYPE_CREEPER_HEAD = 3;
	public const TYPE_BURST = 4;
	public const COLOR_BLACK = "\x00";
	public const COLOR_RED = "\x01";
	public const COLOR_DARK_GREEN = "\x02";
	public const COLOR_BROWN = "\x03";
	public const COLOR_BLUE = "\x04";
	public const COLOR_DARK_PURPLE = "\x05";
	public const COLOR_DARK_AQUA = "\x06";
	public const COLOR_GRAY = "\x07";
	public const COLOR_DARK_GRAY = "\x08";
	public const COLOR_PINK = "\x09";
	public const COLOR_GREEN = "\x0a";
	public const COLOR_YELLOW = "\x0b";
	public const COLOR_LIGHT_AQUA = "\x0c";
	public const COLOR_DARK_PINK = "\x0d";
	public const COLOR_GOLD = "\x0e";
	public const COLOR_WHITE = "\x0f";

	public function __construct(int $meta = 0){
		parent::__construct(ItemIds::FIREWORKS, $meta, "Fireworks");
	}

	public function getFlightDuration() : int{
		return $this->getExplosionsTag()->getByte("Flight", 1);
	}

	public function getRandomizedFlightDuration() : int{
		return ($this->getFlightDuration() + 1) * 10 + mt_rand(0, 5) + mt_rand(0, 6);
	}

	public function setFlightDuration(int $duration) : void{
		$this->getNamedTag()->setTag('Fireworks', $this->getExplosionsTag()->setByte('Flight', $duration));
	}

	protected function getExplosionsTag() : CompoundTag{
		return $this->getNamedTag()->getCompoundTag('Fireworks') ?? new CompoundTag();
	}

	public function addExplosion(int $type, string $color, string $fade = "", int $flicker = 0, int $trail = 0) : void{
		$explosion = new CompoundTag();
		$explosion->setByte("FireworkType", $type);
		$explosion->setByteArray("FireworkColor", $color);
		$explosion->setByteArray("FireworkFade", $fade);
		$explosion->setByte("FireworkFlicker", $flicker);
		$explosion->setByte("FireworkTrail", $trail);
		$tag = $this->getExplosionsTag();
		$explosions = $tag->getListTag('Explosions') ?? new ListTag();
		$explosions->push($explosion);
		$tag->setTag('Explosions', $explosions);
		$this->getNamedTag()->setTag('Fireworks', $tag);
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		$nbt = EntityFactory::createBaseNBT($blockReplace->getPos()->add(0.5, 0, 0.5), new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
		$entity = EntityFactory::create(FireworkRocket::class, $player->getWorld(), $nbt, $this);
		if($entity instanceof Entity){
			--$this->count;
			$entity->spawnToAll();
			return ItemUseResult::SUCCESS();
		}
		return ItemUseResult::NONE();
	}
}