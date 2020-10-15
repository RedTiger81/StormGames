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

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use StormGames\SGCore\permission\DefaultPermissions;

class KitManager{
	/** @var Kit[] */
	public static $kits = [];

	public static function init(): void{
		self::register(new KitFAMOUS(11, ItemFactory::get(ItemIds::COAL)));
		self::register(new KitVIP(12, ItemFactory::get(ItemIds::IRON_INGOT)));
		self::register(new KitVIP2(13, ItemFactory::get(ItemIds::GOLD_INGOT)));
		self::register(new KitMVP(14, ItemFactory::get(ItemIds::EMERALD)));
		self::register(new KitMVP2(15, ItemFactory::get(ItemIds::DIAMOND)));

		self::initPermissions();
	}

	public static function initPermissions(): void{
		$kits = DefaultPermissions::registerPermission(new Permission(DefaultPermissions::ROOT . "kit." . 'all', null, Permission::DEFAULT_OP), PermissionManager::getInstance()->getPermission(DefaultPermissions::ADMIN));
		foreach(self::$kits as $kit){
			DefaultPermissions::registerPermission(new Permission($kit->getPermission(), null, Permission::DEFAULT_FALSE), $kits);
		}
	}

	public static function register(Kit $kit): void{
		$kit->initContents();
		self::$kits[$kit->slot] = $kit;
	}

	public static function get(): array{
		return self::$kits;
	}
}