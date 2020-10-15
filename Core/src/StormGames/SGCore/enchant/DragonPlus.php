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

namespace StormGames\SGCore\enchant;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\math\Vector3;
use pocketmine\world\Position;

class DragonPlus extends CustomEnchantment{
	public $fakeItem = null;

	public function blockBreak(BlockBreakEvent $event, int $level) : void{
		if($this->fakeItem === null){
			$this->fakeItem = clone $event->getItem();
			$this->fakeItem->getNamedTag()->setByte("fake", 1);
			$this->fakeItem->removeEnchantments();
			$this->fakeItem->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantment::FURNACE)));
		}

		foreach(self::getBlocks($event->getBlock()->getPos()) as $block){
			$block->getPos()->getWorld()->useBreakOn($block->getPos(), $this->fakeItem, $event->getPlayer(), false);
		}
	}

	/**
	 * @param Position $position
	 *
	 * @param int      $i
	 *
	 * @return Block[]
	 */
	public static function getBlocks(Position $position, int $i = 1): array {
		$blocks = [];
		$min = $position->add(-$i, -$i, $i);
		$max = $position->add($i, $i, -$i);
		$minX = min($min->x, $max->x);
		$maxX = max($min->x, $max->x);
		$minY = min($min->y, $max->y);
		$maxY = max($min->y, $max->y);
		$minZ = min($min->z, $max->z);
		$maxZ = max($min->z, $max->z);
		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				for($y = $minY; $y <= $maxY; ++$y){
					$blocks[] = $position->getWorld()->getBlock(new Vector3($x, $y, $z));
				}
			}
		}
		return $blocks;
	}
}