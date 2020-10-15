<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity\utils;

use pocketmine\entity\Skin;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;

class Skins{
    public const MODEL_PLAYER = "player";
    public const MODEL_TABLE = "table";
    public const MODEL_WHALE = "whale";
    public const MODEL_BLOCK = "block";
    public const MODEL_SONIC = "sonic";
    public const MODEL_RUMBLE = "rumble";
    public const MODEL_PIKACHU = "pikachu";

    public const CAPE_TURKISH_FLAG = "tr_flag";

    /** @var Skin[][] */
    protected static $skins = [];

    /** @var string */
    protected static $geometryData;
    /** @var array */
    protected static $geometryDataDecoded;

    /** @var string[] */
    private static $capes;

    public static function fromFile(string $filename) : string{
	    $image = imagecreatefrompng($filename);
	    $combine = [];
	    for($y = 0; $y < imagesy($image); $y++){
		    for($x = 0; $x < imagesx($image); $x++){
			    $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
			    $color['alpha'] = (($color['alpha'] << 1) ^ 0xff) - 1; // back = (($alpha << 1) ^ 0xff) - 1
			    $combine[] = sprintf("%02x%02x%02x%02x", $color['red'], $color['green'], $color['blue'], $color['alpha'] ?? 0);
		    }
	    }
	    $data = hex2bin(implode('', $combine));
	    return $data;
    }

    public static function init() : void{
        /* Loading Geometry */
        self::$geometryData = file_get_contents(SGCore::getAPI()->getResourcesDir() . "geometries.json");
        self::$geometryDataDecoded = json_decode(self::$geometryData, true);

        /* Loading Cape Data */
        foreach(glob(SGCore::getAPI()->getResourcesDir() . 'capes/*.png') as $cape){
            self::$capes[basename($cape, ".png")] = self::fromFile($cape);
        }

        /* Loading Skin Data */
        foreach(json_decode(file_get_contents(SGCore::getAPI()->getResourcesDir() . "skinData.json"), true) as $model => $skins){
            foreach($skins as $name => $skinData){
                if($name !== 'geometry_name'){
                    self::$skins[$model][$name] = self::decode("$model.$name", $skinData, $skins["geometry_name"] ?? "");
                }
            }
        }
    }

    public static function getSkin(string $skinName, string $model = Skins::MODEL_PLAYER) : ?Skin{
        return self::$skins[$model][$skinName] ?? null;
    }

    public static function getCape(string $name) : ?string{
        return self::$capes[$name] ?? null;
    }

    public static function getCapes() : array{
        return self::$capes;
    }

    public static function canUseCape(SGPlayer $player, string $cape) : bool{
        return $cape === self::CAPE_TURKISH_FLAG ?: $player->isVip();
    }

    public static function getGeometry(string $geometryName) : array{
    	if(!isset(self::$geometryDataDecoded[$geometryName])){
    		return [];
	    }
	    $data = "";
	    foreach(self::$geometryDataDecoded as $name => $data1){
	    	if($name === $geometryName){
	    		$data = "{\"$name\": " . json_encode($data1) . "}";
	    		break;
		    }
	    }
        return [$geometryName, $data];
    }

    public static function decode(string $id, string $data, string $geometryName) : Skin{
        return new Skin($id, self::decodeData($data), '', ...self::getGeometry($geometryName));
    }

    /* NOW NOT USE
    private static function encodeData(string $data) : string{
        return base64_encode(zlib_encode($data, ZLIB_ENCODING_DEFLATE, 9));
    }
    */

    private static function decodeData(string $data) : string{
        return zlib_decode(base64_decode($data));
    }

	private static function encodeData($array): string {
    	$json = [];
		foreach($array as $k => $v) {
			if(strlen(strval($k))) {
				if(is_array($v)) {
					$json[$k] = self::encodeData($v); //RECURSION
				} else {
					$json[$k] = $v;
				}
			}
		}
		return str_replace(['\\', "\"[\"", "\"}\""], ['', "[", "]"], json_encode($json));
	}

}