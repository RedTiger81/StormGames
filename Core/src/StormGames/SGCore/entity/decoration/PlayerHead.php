<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity\decoration;

use pocketmine\entity\Skin;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use StormGames\SGCore\entity\Decoration;
use StormGames\SGCore\entity\RDHuman;
use StormGames\SGCore\entity\utils\Skins;

class PlayerHead extends RDHuman implements Decoration{

	/** @var int */
	public $height = 8, $width = 8;
	/** @var int */
	protected $gravity = 0;

	public function __construct(World $level, CompoundTag $nbt){
		$this->setHead($nbt->getString("Head"));
		parent::__construct($level, $nbt);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(1);

		parent::initEntity($nbt);
	}

	/**
	 * @param string $head
	 * @param Player|Skin $data
	 */
	public function setHead(string $head, $data = null) : void{
		if($head === "player" or $head === "human"){
			if($data !== null){
				if($data instanceof Player){
					$this->setSkin($data->getSkin());
				}elseif($data instanceof Skin){
					$this->setSkin($data);
				}
			}
		}else{
			$this->setSkin(Skins::getSkin($head));
		}
	}

	public function setSkin(Skin $skin) : void{
		parent::setSkin(new Skin($skin->getSkinId(), $skin->getSkinData(), '', ...Skins::getGeometry("player_head")));
	}

	public function hasMovementUpdate() : bool{
		return false;
	}

	protected function doHitAnimation() : void{

	}
}