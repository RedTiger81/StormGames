<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\block\Block;
use pocketmine\form\FormIcon;
use pocketmine\item\Item;
use StormGames\SGCore\SGCore;

class IconUtils{
    private static $list;

    public static function init(SGCore $core) : void{
        $dir = $core->getResourcesDir() . 'icons.json';
        self::$list = json_decode(file_get_contents($dir), true);
    }

    public static function get(string $path, string $ext = '.png') : FormIcon{
    	return new FormIcon('https://cdn.stormgames.net/forms/' . $path . $ext);
    }

    public static function getItem($id, int $data = 0) : string{
        if($id instanceof Block or $id instanceof Item){
            $data = $id->getMeta();
            $id = $id->getId();
        }

        return self::$list[self::encode($id, $data)] ?? '';
    }

    public static function getFormIcon($id, int $data = 0) : FormIcon{
        $get = self::getItem($id, $data);
        return new FormIcon($get, substr($get, 0, 4) === "http" ? FormIcon::IMAGE_TYPE_URL : FormIcon::IMAGE_TYPE_PATH);
    }

    private static function encode(int $id, int $variant) : int{
        return ($id << 4) | $variant;
    }
}