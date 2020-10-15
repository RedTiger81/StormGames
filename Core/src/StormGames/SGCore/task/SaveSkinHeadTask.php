<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use StormGames\SGCore\SGPlayer;

class SaveSkinHeadTask extends AsyncTask{

	/** @var string */
	private $name;
	/** @var string */
	private $skinData;

	public function __construct(SGPlayer $player){
		$this->name = $player->getLowerCaseName();
		$this->skinData = $player->getSkin()->getSkinData();
	}

	public function onRun() : void{
		switch(strlen($this->skinData)){
			case 8192:
			case 16384:
			default: // for phpstorm
				$maxX = $maxY = 8;
				$width = 64;
				$uv = 32;
				break;
			case 65536:
				$maxX = $maxY = 16;
				$width = 128;
				$uv = 64;
		}

		$skin = substr($this->skinData, ($pos = ($width * $maxX * 4)), $pos);

		$image = imagecreatetruecolor($maxX, $maxY);

		for($y = 0; $y < $maxY; ++$y){
			for($x = 0; $x <= $maxX; ++$x){
				// layer 1
				$key = (($width * $y) + $maxX + $x) * 4;
				// layer 2
				$key2 = (($width * $y) + $maxX + $x + $uv) * 4;
				$a = ord($skin{$key2 + 3});
				if($a >= 127){ // if layer 2 pixel is opaque enough, use it instead.
					$r = ord($skin{$key2});
					$g = ord($skin{$key2 + 1});
					$b = ord($skin{$key2 + 2});
				}else{
					$r = ord($skin{$key});
					$g = ord($skin{$key + 1});
					$b = ord($skin{$key + 2});
				}
				imagesetpixel($image, $x, $y, imagecolorallocate($image, $r, $g, $b));
			}
		}

		$path = __DIR__ . DIRECTORY_SEPARATOR . $this->name . ".png";
		imagepng($image, $path);
		Internet::postURL("http://cdn.stormgames.net/updateHead", [
			"head" => $this->name,
			"result" => base64_encode(file_get_contents($path)),
			"pass" => "merhaba"
		]);
		unlink($path);
	}

	public function setGarbage(){}
}