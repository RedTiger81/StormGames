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

namespace StormGames\Kit;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemIds;

class KitVIP2 extends Kit{
	public function getName() : string{
		return self::KIT_VIP_PLUS;
	}

	public function initContents() : void{
		$items = [];
		$items[] = $this->getItem(ItemIds::DIAMOND_HELMET, [[3 => Enchantment::PROTECTION()], [2 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_CHESTPLATE, [[3=> Enchantment::PROTECTION()], [2=> Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_LEGGINGS, [[3 => Enchantment::PROTECTION()], [2 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_BOOTS, [[3 => Enchantment::PROTECTION()], [2 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_SWORD, [[3 => Enchantment::SHARPNESS()], [2 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_AXE, [[3 => Enchantment::SHARPNESS()], [2 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_PICKAXE, [[3 => Enchantment::EFFICIENCY()], [2 => Enchantment::UNBREAKING()]]);
		$items[] = $this->getItem(ItemIds::DIAMOND_HOE, [[3 => Enchantment::EFFICIENCY()], [2 => Enchantment::UNBREAKING()]]);
		$this->contents = $items;
	}
}