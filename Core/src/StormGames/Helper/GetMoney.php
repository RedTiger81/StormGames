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

class GetMoney extends BaseBook{
	public function init(): void{
		$s = function(...$text){
			foreach($text as $text1){
				$this->setPageTextV2($text1);
			}
		};
		$s("§l§bStorm§cGames" . "\n§r§3" . "Tekrar merhaba! Sana en hızlı para kazanma yöntemlerini anlatacağım hazırsan başlayalım.",
			"§l§bÇiftçilik (/oy)\n§r§7» §8Kendi adanda ekip biçerek para kazanabilirsin.\n\n" .
			"§l§bOy (/oy)\n§r§7» §8Sunucumuza oy verip para ve oy kasası kazanabilirsin.\n\n" . 
		    "§l§4Davet et\n§r§7» §8Arkadaşlarını sunucuya davet et 2.500$ kazan, tek yapması gereken oyuna girince ekrana senin adını yazmak.");
	}

	public function getTitle() : string{
		return "Para kazanma rehberi";
	}
}