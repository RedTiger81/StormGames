<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use StormGames\SGCore\SGCore;
use StormGames\SGCore\utils\Utils;
use function count;
use function current;
use function key;
use function is_callable;
use function array_keys;
use function implode;

class Top{
	public static function money(int $limit = 10) : array{
		return self::top(SGCore::TABLE_PLAYERS, ['username' => function(string $username){
			return Utils::getUsername($username);
		}, 'money' => function($money) : string{
			return Utils::addMonetaryUnit($money + 0);
		}], 'ORDER BY money DESC LIMIT ' . $limit);
	}

	public static function kills(int $limit = 10) : array{
		return self::top(SGCore::TABLE_PLAYERS, ['username' => function(string $username){
			return \StormGames\SGCore\utils\Utils::getUsername($username);
		}, 'kills' => true], 'ORDER BY kills DESC LIMIT ' . $limit);
	}

	public static function deaths(int $limit = 10) : array{
		return self::top(SGCore::TABLE_PLAYERS, ['username' => function(string $username){
			return \StormGames\SGCore\utils\Utils::getUsername($username);
		}, 'deaths' => true], 'ORDER BY deaths DESC LIMIT ' . $limit);
	}


	private static function top(string $table, array $columns, string $extra) : array{
		$list = [];

		$countOne = count($columns) === 1;
		$result = SGCore::getDatabase()->select($table, implode(', ', array_keys($columns)), $extra);
		if(($result->num_rows ?? 0) !== 0){
			while($row = $result->fetch_assoc()){
				if($countOne){
					$data = current($columns);
					$key = key($columns);
					$new = is_callable($data) ? $data($row[$key]) : $row[$key];
				}else{
					$new = [];
					foreach($columns as $key => $data){
						$new[] = is_callable($data) ? $data($row[$key]) : $row[$key];
					}
				}
				$list[] = $new;
			}
		}

		return $list;
	}

}