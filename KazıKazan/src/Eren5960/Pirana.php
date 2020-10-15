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

namespace Eren5960;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Pirana{
	public static $session = [];
	public static $data = [];

	public static function init(): void{
		self::$data[ItemIds::COAL] = 250;
		self::$data[ItemIds::IRON_INGOT] = 500;
		self::$data[ItemIds::GOLD_INGOT] = 750;
		self::$data[ItemIds::DIAMOND] = 1000;
		self::$data[ItemIds::EMERALD] = 3000;
	}

	public static function rand(): Item{
		$rand = array_rand(self::$data);
		return ItemFactory::get($rand)->setCustomName("§e" . self::$data[$rand] . "$");
	}
}