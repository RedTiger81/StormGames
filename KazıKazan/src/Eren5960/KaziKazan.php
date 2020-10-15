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

use onebone\economyapi\EconomyAPI;
use jojoe77777\FormAPI\FormAPI;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class KaziKazan extends PluginBase{
	public const KAZI_KAZAN = 'IF9fX18gIF9fLiAgICAgICAgICAgICAuX18gX19fXyAgX18uICAgICAgICAgICAgICAgICAgICAgICAgICAgIA0KfCAgICB8LyBfX19fX18gIF9fX19fX198X198ICAgIHwvIF9fX19fXyAgX19fX19fX19fX19fICAgIF9fX18gIA0KfCAgICAgIDwgXF9fICBcIFxfX18gICB8ICB8ICAgICAgPCBcX18gIFwgXF9fXyAgIFxfXyAgXCAgLyAgICBcIA0KfCAgICB8ICBcIC8gX18gXF8vICAgIC98ICB8ICAgIHwgIFwgLyBfXyBcXy8gICAgLyAvIF9fIFx8ICAgfCAgXA0KfF9fX198X18gKF9fX18gIC9fX19fXyB8X198X19fX3xfXyAoX19fXyAgL19fX19fIChfX19fICB8X19ffCAgLw0KICAgICAgICBcLyAgICBcLyAgICAgIFwvICAgICAgICAgIFwvICAgIFwvICAgICAgXC8gICAgXC8gICAgIFwvIA==';
	/** @var KaziKazan */
	public static $api;

	public function onLoad(){
		self::$api = $this;
	}

	public static function getAPI(): KaziKazan{
		return self::$api;
	}

	public function onEnable(){
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		Pirana::init();
		$this->getServer()->getCommandMap()->register('kazikazan', new Nutella("kazikazan", "Kazı ve kazan!"));
	}
}