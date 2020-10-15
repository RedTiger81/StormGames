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

class LevelBook extends BaseBook{
	public function init(): void{
		$s = function(...$text){
			foreach($text as $text1){
				$this->setPageTextV2($text1);
			}
		};
		$s("§l§bStorm§cGames" . "\n§r§3" . "Tekrar merhaba! Sana nasıl stormgames leveli kazanacağını anlatacağım hazırsan başlayalım.\n\n",
			"§l§8» §r§1/görev §8: §2Günlük görevlerden istediğini seç ve yapmaya başla bu sana §d15 XP §2kazandıracak.",
			"§l§8» §r§1/w a §8: §2Arenda oyuncu öldürdükçe §d2 XP §2kazanacaksın.");
	}

	public function getTitle() : string{
		return "Level kazanma rehberi";
	}
}