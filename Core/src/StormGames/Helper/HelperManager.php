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

namespace StormGames\Helper;

use pocketmine\item\ItemIds;
use StormGames\SGCore\SGPlayer;

class HelperManager{
	/** @var BaseBook[] */
	public static $books = [];

	public static function init(): void{
		self::registerBook(FirstBook::class);
		self::registerBook(GetMoney::class);
		self::registerBook(LevelBook::class);
	}

	public static function registerBook(string $book, bool $override = false): void{
		if(isset(self::$books[$book]) && !$override){
			throw new \InvalidStateException("{$book} already registered");
		}
		/** @var BaseBook $bookClass */
		$bookClass = new $book(ItemIds::WRITTEN_BOOK, 0, "StormGames");
		$bookClass->init();
		self::$books[$book] = $bookClass;
	}

	public static function get(string $class): BaseBook{
		return self::$books[$class];
	}

	public static function add(SGPlayer $player, string $class, string $nameForComp = 'first'): void{
		$player->getInventory()->addItem(self::get($class)->setCustomName($player->translate("forms.book." . $nameForComp)));
	}

	public static function addAll(SGPlayer $player){
		self::add($player, FirstBook::class);
		self::add($player, GetMoney::class, 'getMoney');
		self::add($player, LevelBook::class, 'level');
	}
}

