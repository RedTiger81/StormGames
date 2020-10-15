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

class FirstBook extends BaseBook{
	public function init(): void{
		$s = function(...$text){
			foreach($text as $text1){
				$this->setPageTextV2($text1);
			}
		};
		$s("§l§bStorm§cGames" . "\n§r" .
		"Selam dostum! Şimdi diyeceksin ki bu kitap niye var... Tamam haklısın ama StormGames farklı! Çoğu sunucu gibi gibi değil, §4bu kitabı kesinlikle okumalısın.\n\n" .
		"§aHadi genel komutları öğrenelim!\n§r");
		$s("» §1/w§r : Bölgeler arası ışınlanma komutu, Orman'a bi bak derim ;)\n" .
			"» §1/p§r : Arsa dünyasına ışınlanma ve yönetme komutu, Arsanızın üst katına yapılarınızı yapın orada daha rahat gözükecek.\n" ,
			"» §1/ada§r : SkyBlock ana komutu. Güzel UI sayfası ile oldukça rahat ve kolay!\n" .
			"» §1/a§r : Anvil ama bu bir büyücü olmalı! Kendine özel büyüleriyle de tamamen eşsiz oyun deneyimi.\n" ,
			"» §1/e§r : Ekonomi işlemleri için genel komut. Burada satın alıp, satabilir. Para gönderip, alabilirsiniz. \n" .
			"» §1/re§r : Sadece bir tıkla yapı yapmak bi hayaldi gerçek oldu! Sadece ormanda geçerli." ,
			"» §1/menu§r : Profiliniz, kozmetikler, petler ve daha fazlası burada.\n" .
			"» §1/vote§r : Sunucuya oy verip bu komutu kullandığınızda para ve oy kasası kazanırsınız!\n" ,
			"» §1/msg§r : Oyunculara mesaj atmak için kullan hemde messenger gibi!\n" .
			"» §1/xyz§r : Her an kordinatını görmek ister misin? Bence istersin çünkü ormanda lazım olacak.\n" ,
			"» §1/görev§r : Görev yaparak bir çok ödül kazanın!\n" ,
			"» §1/enler§r : Sunucunun en iyilerine bi bak sende oraya girmek için tüm gücünü göster!");
	}

	public function getTitle() : string{
		return "Yardım";
	}
}