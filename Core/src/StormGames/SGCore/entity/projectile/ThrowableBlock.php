<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity\projectile;

use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;

class ThrowableBlock extends Throwable{
	public const NETWORK_ID = EntityLegacyIds::MINECART;

	public const TAG_BLOCK_ID = "DisplayBlockID"; // int
	public const TAG_BLOCK_META = "DisplayBlockMeta"; // int

	/** @var int */
	public $width = 1, $height = 1;

	/** @var int */
	protected $blockId, $blockMeta;
	/** @var callable */
	protected $callable = null;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setBlock($nbt->getInt(self::TAG_BLOCK_ID, 0), $nbt->getInt(self::TAG_BLOCK_ID, 0));
		$this->setInvisible();
		$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		if(is_callable($this->callable)){
			($this->callable)($this);
		}
	}

	public function setBlock(int $id, int $meta = 0) : void{
		$this->blockId = $id;
		$this->blockMeta = $meta;

		if($id !== 0){
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, RuntimeBlockMapping::toStaticRuntimeId($id, $meta));
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_OFFSET, 6);
			$this->getNetworkProperties()->setByte(EntityMetadataProperties::MINECART_HAS_DISPLAY, 1);
		}else{
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_BLOCK, 0);
			$this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_OFFSET, 0);
			$this->getNetworkProperties()->setByte(EntityMetadataProperties::MINECART_HAS_DISPLAY, 0);
		}
	}

	/**
	 * @return callable
	 */
	public function getCallable() : ?callable{
		return $this->callable;
	}

	/**
	 * @param callable $callable
	 */
	public function setCallable(?callable $callable) : void{
		$this->callable = $callable;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt(self::TAG_BLOCK_ID, $this->blockId);
		$nbt->setInt(self::TAG_BLOCK_META, $this->blockMeta);
		return $nbt;
	}
}