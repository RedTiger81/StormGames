<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGCore;

class MySQL{
	/** @var \mysqli */
	protected $db;

	public function __construct(string $host, string $username, string $password, string $dbName, int $port){
		$this->db = new \mysqli($host, $username, $password, '', $port);

		assert(!$this->db->connect_error);
		$this->db->set_charset("utf-8");
		$this->db->query("SET NAMES 'utf8' COLLATE 'utf8_turkish_ci'");
		$this->selectDBName($dbName);
		SGCore::getAPI()->getLogger()->info("MySQL > " . $host . TextFormat::GRAY . " sunucusu ile bağlantı kuruldu!");
	}

	public function selectDBName(string $dbName) : void{
		$this->db->query("CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8 COLLATE utf8_turkish_ci");
		$this->db->select_db($dbName);
	}

	public function createTable(string $table, array $contents){
		$create = 'CREATE TABLE IF NOT EXISTS ' . $table . ' (';
		foreach($contents as $name => $content){
			$create .= $name . ' ' . $content . ', ';
		}
		return $this->query(substr($create, 0, -2) . ')');
	}

	public function select(string $table, string $column = '*', string $extra = ''){
		return $this->query("SELECT $column FROM $table $extra");
	}

	public function insert(string $table, array $data){
		$columns = $values = '';
		foreach($data as $key => $value){
			$columns .= $key . ', ';
			$values .= (is_bool($value) || is_numeric($value) ? $value + 0 : "'$value'") . ', ';
		}
		$columns = substr($columns, 0, -2);
		$values = substr($values, 0, -2);
		return $this->query("INSERT INTO $table ($columns) VALUES ($values)");
	}

	public function query(string $query, int $resultMode = MYSQLI_STORE_RESULT){
		return $this->db->query($query, $resultMode);
	}

	public function updateDatas(array $data, string $tableName, string $extra = ''){
		$values = rawurldecode(str_replace('=', '=\'', http_build_query($data, '', '\', ', PHP_QUERY_RFC3986))) . '\'';
		return $this->query("UPDATE $tableName SET $values $extra");
	}

	public function printError($value, string $query){
		if($this->db->error !== ''){
			var_dump($query, $this->db->error);
		}

		return $value;
	}

	public function getDB() : \mysqli{
		return $this->db;
	}
}