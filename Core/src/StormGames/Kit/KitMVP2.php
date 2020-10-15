<?php
/*
 *  _____               _               ___   ___  __ 
 * /__   \___  _ __ ___| |__   /\/\    / __\ / _ \/__\
 *   / /\/ _ \| '__/ __| '_ \ /    \  / /   / /_)/_\  
 *  / / | (_) | | | (__| | | / /\/\ \/ /___/ ___//__  
 *  \/   \___/|_|  \___|_| |_\/    \/\____/\/   \__/
 *
 * (C) Copyright 3019 TorchMCPE (http://torchmcpe.fun/) and others.
 *
 * Licensed under the Apache License, Version 3.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-3.0
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

namespace StormGames\Kit;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemIds;
use StormGames\SGCore\enchant\IceAspect;

class KitMVP2 extends Kit{
	public function getName() : string{
		return self::KIT_MVP_PLUS;
	}

	public function initContents() : void{
		$items = [];
		$items[] = $this->getItem(ItemIds::DIAMOND_HELMET, [[4 => Enchantment::PROTECTION()], [4 => Enchantment::UNBREAKING()], [3 => Enchantment::PROJECTILE_PROTECTION()], [1 => Enchantment::get(IceAspect::ANTI_KNOCKBACK)], [2 => Enchantment::get(IceAspect::THORNS)]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_CHESTPLATE, [[4 => Enchantment::PROTECTION()], [4 => Enchantment::UNBREAKING()], [3 => Enchantment::PROJECTILE_PROTECTION()], [1 => Enchantment::get(IceAspect::ANTI_KNOCKBACK)], [2 => Enchantment::get(IceAspect::THORNS)]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_LEGGINGS, [[4 => Enchantment::PROTECTION()], [4 => Enchantment::UNBREAKING()], [3 => Enchantment::PROJECTILE_PROTECTION()], [1 => Enchantment::get(IceAspect::ANTI_KNOCKBACK)], [2 => Enchantment::get(IceAspect::THORNS)]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_BOOTS, [[4 => Enchantment::PROTECTION()], [4 => Enchantment::UNBREAKING()], [3 => Enchantment::PROJECTILE_PROTECTION()], [1 => Enchantment::get(IceAspect::ANTI_KNOCKBACK)], [2 => Enchantment::get(IceAspect::THORNS)]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_SWORD, [[4 => Enchantment::SHARPNESS()], [4 => Enchantment::UNBREAKING()], [2 => Enchantment::KNOCKBACK()], [2 => Enchantment::get(IceAspect::ICE_ASPECT)], [1 => Enchantment::get(IceAspect::VAMPIRE)]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_AXE, [[4 => Enchantment::SHARPNESS()], [4 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_PICKAXE, [[4 => Enchantment::EFFICIENCY()], [4 => Enchantment::UNBREAKING()], [1 => Enchantment::get(IceAspect::FURNACE)], [1 => Enchantment::get(IceAspect::ENERGIZING)]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_HOE, [[4 => Enchantment::EFFICIENCY()], [4 => Enchantment::UNBREAKING()], [1 => Enchantment::SILK_TOUCH()]]);
		$this->contents = $items;
	}
}