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

namespace StormGames\Rank;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use StormGames\SGCore\permission\DefaultPermissions;

class RankManager{
	/** @var Rank[] */
	public static $ranks = [];
	/** @var Rank[] */
	public static $perms = [];

	public static function init(): void{
		self::register(new Rank("coal", 50, 300, 20, 6)); // 50 => 50.000 $
		self::register(new Rank("redstone", 150, 500, 30, 12));
		self::register(new Rank("iron", 200, 800, 40, 24));
		self::register(new Rank("gold", 250, 1000, 50, 36));
		self::register(new Rank("emerald", 500, 1500, 60, 48));
		self::register(new Rank("diamond", 750, 2500, 80, 56));
		self::register(new Rank("legendary", 1000, 5000, 100, 64));
		self::initPermissions();
	}

	public static function register(Rank $rank){
		self::$ranks[] = $rank;

	}

	public static function initPermissions(): void{
		$admin = PermissionManager::getInstance()->getPermission(DefaultPermissions::ADMIN);
		foreach(self::$ranks as $rank){
			DefaultPermissions::registerPermission(new Permission($rank->getPermission(), null, Permission::DEFAULT_FALSE), $admin);
			self::$perms[$rank->getPermission()] = $rank;
		}
	}

	public static function getRank(int $rank): ?Rank{
		if($rank === -1){
			return new Rank("empty", 0, 0, 0, 0);
		}
		return self::$ranks[$rank] ?? null;
	}
}