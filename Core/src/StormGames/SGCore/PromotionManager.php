<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use StormGames\SGCore\utils\MySQLValidator;
use StormGames\SGCore\utils\TextUtils;

class PromotionManager{

    public const ERROR_CODE_NOT_FOUND = -1;
    public const ERROR_USED_ALL_CODES = 0;
    public const ERROR_USED = 1;

    /** @var array */
    private static $codes = [];

    public static function init() : void{
        $validator = MySQLValidator::get(SGCore::TABLE_PROMOTIONS);
        $selected = SGCore::getDatabase()->select(SGCore::TABLE_PROMOTIONS);

        if(($selected->num_rows ?? 0) !== 0){
            while($currentRow = $selected->fetch_assoc()){
                self::$codes[$currentRow['code']] = $validator->translateWithCallable($currentRow);
            }
        }
    }

    public static function createCode(int $coins, int $usableCount = 5000) : string{
        do{
            $code = bin2hex(random_bytes(4));
        }while(self::hasCode($code));

        self::addCode($code, $coins, $usableCount, true);
        return $code;
    }

    public static function addCode(string $code, int $coins, int $usableCount = 5000, bool $force = false) : bool{
        if($force or !self::hasCode($code)){
            self::$codes[$code] = [
                'usedPlayers' => [],
                'usableCount' => $usableCount,
                'coins' => $coins
            ];
	        $selected = [
		        'code' => $code,
		        'usedPlayers' => '',
		        'usableCount' => $usableCount,
		        'coins' => $coins
	        ];
            return MySQLValidator::get(SGCore::TABLE_PROMOTIONS)->insert(SGCore::getDatabase(), $selected) !== false;
        }

        return false;
    }

    public static function hasCode(string $code) : bool{
        return isset(self::$codes[$code]);
    }

    public static function removeCode(string $code) : bool{
        return self::hasCode($code) ? (SGCore::getDatabase()->query('DELETE FROM ' . SGCore::TABLE_PROMOTIONS . ' WHERE code=\'' . $code . '\'') !== false) : false;
    }

    public static function useCode(string $code, SGPlayer $player) : int{
        if(self::hasCode($code)){
            $info = self::$codes[$code];
            $name = $player->getLowerCaseName();
            if((count($info['usedPlayers']) + 1) > $info['usableCount']){
                return self::ERROR_USED_ALL_CODES;
            }
            if(in_array($name, $info['usedPlayers'], true)){
                return self::ERROR_USED;
            }
            $info['usedPlayers'][] = $player->getLowerCaseName();
            $info['usableCount'] -= 1;
            $player->addCoins($info["coins"]);

            self::$codes[$code] = $info;
            self::updateCode($code, true);

            return $info['coins'];
        }

        return self::ERROR_CODE_NOT_FOUND;
    }

    public static function updateCode(string $code, bool $force = false) : void{
        if($force or self::hasCode($code)){
            $info = self::$codes[$code];
            MySQLValidator::get(SGCore::TABLE_PROMOTIONS)->update(SGCore::getDatabase(), [
                'usedPlayers' => TextUtils::fromArray($info['usedPlayers']),
                'usableCount' => $info['usableCount'],
                'coins' => $info['coins']
            ], 'WHERE code=\''. $code . '\'');
        }
    }
}