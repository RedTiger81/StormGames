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
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\permission\DefaultPermissions;

abstract class Kit{
	public const KIT_FAMOUS = TextFormat::RED . "FAMOUS";
	public const KIT_VIP = TextFormat::GOLD . "VIP";
	public const KIT_VIP_PLUS = TextFormat::GOLD . "VIP+";
	public const KIT_MVP = TextFormat::GOLD . "MVP";
	public const KIT_MVP_PLUS = TextFormat::GOLD . "MVP+";

	public const NOT_CLAIM_PERM = 0;
	public const CLAIM_SUCCESS = 1;

	/** @var int */
	public $slot;
	/** @var Item */
	public $item;
	/** @var Item[] */
	protected $contents = [];

	/**
	 * @param int   $slot
	 * @param Item  $item
	 */
	public function __construct(int $slot, Item $item){
		$this->slot = $slot;
		$this->item = $item->addEnchantment(new EnchantmentInstance(Enchantment::INFINITY()))->setCustomName($this->getName());
	}

	/**
	 * @return Item[]
	 */
	public function getContents() : array{
		return $this->contents;
	}

	/**
	 * @param int   $id
	 * @param Enchantment[] $enchantments
	 * @param int   $meta
	 *
	 * @return Item
	 */
	public function getItem(int $id, array $enchantments = [], $meta = 0): Item{
		$item = ItemFactory::get($id, $meta)->setCustomName("§s" .$this->getName() . " Kiti");
		foreach($enchantments as $data){
			foreach($data as $level => $enchantment){
				$item->addEnchantment(new EnchantmentInstance($enchantment, $level ?? 1));
			}
		}
		return $item;
	}

	public function getPermission(): string{
		return DefaultPermissions::ROOT . "kit." . str_replace(" ", "_", strtolower(TextFormat::clean($this->getName())));
	}

	public function canClaim(SGPlayer $player): int{
		return $player->hasPermission($this->getPermission()) ? ($player->kitTime <= time() ? self::CLAIM_SUCCESS : $player->kitTime) : self::NOT_CLAIM_PERM;
	}

	abstract public function getName(): string;
	abstract public function initContents(): void;
}