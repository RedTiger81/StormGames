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

use pocketmine\item\WritableBookBase;
use pocketmine\item\WrittenBook;

abstract class BaseBook extends WrittenBook{
	public $current = 0;

	public function setPageTextV2(string $pageText) : WritableBookBase{
		return parent::setPageText($this->current++, $pageText);
	}

	public function getAuthor() : string{
		return "StormGames";
	}

	public function getTitle() : string{
		return "Kitap";
	}

	public abstract function init(): void;

	public function getVanillaName() : string{
		return "StormGames | " . $this->getTitle();
	}
}