<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use StormGames\SGCore\SGPlayer;

class MySQLValidator{

    /** @var MySQLValidator[] */
    private static $validators = [];

    public static function new(string $table) : MySQLValidator{
        return self::$validators[$table] = new MySQLValidator($table);
    }

    public static function get(string $table) : MySQLValidator{
        return self::$validators[$table];
    }

    /** @var Chain[] */
    private $chain = [];
    /** @var string */
    private $table;

    public function __construct(string $table){
        $this->table = $table;
    }

    public function add(string $name) : Chain{
        return $this->chain[$name] = new Chain($name);
    }

    public function defaults(?SGPlayer $player = null) : array{
        return array_map(function(Chain $chain) use($player){ return $chain->getDefault($player); }, $this->chain);
    }

    public function insert(MySQL $db, array &$defaults = [], array $defaultArgs = [], bool $force = false){
        if(!$force){
            foreach($this->chain as $key => $chain){
                if(isset($defaults[$key])){
                    $defaults[$key] = $this->validateKey($key, $defaults[$key]);
                }else{
                    $defaults[$key] = $chain->getDefault(...$defaultArgs);
                }
            }
        }

        return $db->insert($this->table, $defaults);
    }

    public function select(MySQL $db, string $extra = '', SGPlayer $player = null, bool &$new = false) : array{
        $selected = $db->select($this->table, '*', $extra);
        if(($selected->num_rows ?? 0) === 0){
            $selected = $this->defaults($player);
            $this->insert($db, $selected, [$player], false);
            $new = true;
        }else{
            $new = false;
            $selected = $selected->fetch_assoc();
        }

        return $selected;
    }

    public function selectAll(MySQL $db, string $keyValue, string $extra = '') : array{
        $selected = $db->select($this->table, '*', $extra);
        $data = [];
        if(($selected->num_rows ?? 0) !== 0){
            while($currentRow = $selected->fetch_assoc()){
                $data[$currentRow[$keyValue]] = $this->translateWithCallable($currentRow);
            }
        }

        return $data;
    }

    public function update(MySQL $db, array $data, string $extra = ''){
        return $db->updateDatas($this->validateArray($data), $this->table, $extra);
    }

    public function translateWithCallable(array $array, ...$args) : array{
        foreach($array as $key => $val){
            $array[$key] = $this->translateKey($key, $val, ...$args);
        }

        return $array;
    }

    public function translateKey(string $key, $oldData, ...$args){
        $callable = $this->chain[$key]->getCallback();
        if($callable !== null){
            return $callable($oldData, ...$args);
        }

        return $oldData;
    }

    public function validateKey(string $key, $data){
        $this->chain[$key]->validate($data);
        return $data;
    }

    public function validateArray(array $array) : array{
        foreach($array as $key => $value){
            $this->validateKey($key, $value);
        }

        return $array;
    }

    public function createTable(MySQL $db){
        return $db->createTable($this->table, array_map(function(Chain $chain){
            return $chain->getType();
        }, $this->chain));
    }
}

class Chain{
    public const TYPE_DATA = 0;
    public const TYPE_PLAYER = 1;

    /** @var string */
    private $name, $type;
    /** @var callable */
    private $callable;
    /** @var array */
    private $default = [];
    /** @var callable */
    private $callback = null;

    public function __construct(string $name){
        $this->name = $name;
    }

    public function getName() : string{
        return $this->name;
    }

    /**
     * @return callable
     */
    public function getCallable() : callable{
        return $this->callable;
    }

    public function text() : self{
        $this->type = 'text';
        $this->callable = 'is_string';
        $this->default = ['type' => self::TYPE_DATA, 'data' => ''];

        return $this;
    }

    public function int() : self{
        $this->type = 'int';
        $this->callable = 'is_int';
        $this->default = ['type' => self::TYPE_DATA, 'data' => 0];

        return $this;
    }

    public function bool() : self{
        $this->type = 'bool';
        $this->callable = 'is_bool';
        $this->default = ['type' => self::TYPE_DATA, 'data' => true];

        return $this;
    }

    public function primary() : self{
        $this->type .= ' PRIMARY KEY AUTO_INCREMENT';

        return $this;
    }

    public function default($data, int $type = self::TYPE_DATA) : self{
        $this->validate($data);
        $this->default = ['type' => $type, 'data' => $data];

        return $this;
    }

    public function defaultPlayer(callable $callable) : self{
        return $this->default($callable, self::TYPE_PLAYER);
    }

    public function getDefault(...$args){
        if($this->default['type'] === self::TYPE_PLAYER and $args[0] === null){
            throw new \InvalidArgumentException('Default type is player but player not defined');
        }

        if(is_callable($this->default['data'])){
            return ($this->default['data'])(...$args);
        }else{
            return $this->default['data'];
        }
    }

    public function callback($callback) : self{
        if(is_callable($callback)){
            $this->callback = $callback;
        }elseif(is_bool($callback)){
            $this->callback = $this->callable;
        }else{
            throw new \InvalidArgumentException('Expected: bool or callable but you give ' . gettype($callback));
        }

        return $this;
    }

    /**
     * @return callable
     */
    public function getCallback(): ?callable{
        return $this->callback;
    }

    public function validate($data) : void{
        if(($this->callable)($data) === false and !is_callable($data))
            throw new \InvalidArgumentException($data . ' is not a ' . $this->type);
    }

    public function getType() : string{
        return $this->type;
    }
}