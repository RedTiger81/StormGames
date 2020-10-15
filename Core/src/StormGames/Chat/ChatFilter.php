<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\Chat;

use StormGames\SGCore\SGCore;

class ChatFilter{
    public const CHAT_NONE = 0;
    public const CHAT_BADWORD = 1;
    public const CHAT_AD = 2;

    public const REGEX = '/\b\W?(%s)\W?\b/i';

    /** @var SGCore */
    private $core;
    /** @var string */
    private $badWords, $advertise;

    public function __construct(SGCore $core){
        $this->core = $core;

        $this->badWords = $this->advertise = [];
        foreach(glob(__DIR__ . "/words/*.txt") as $fileName){
            $converted = array_map("trim", file($fileName));
            if(substr(basename($fileName), 0, 3) === "bad"){
                $this->badWords = array_merge($this->badWords, $converted);
            }else{
                $this->advertise = array_merge($this->advertise, $converted);
            }
        }

        $this->badWords = str_replace("||", "|", implode('|', $this->badWords));
        $this->advertise = str_replace("||", "|", implode('|', $this->advertise));
    }

    public function check(string $text) : int{
        if(preg_match($this->buildRegex($this->badWords), $text) === 1){
            return self::CHAT_BADWORD;
        }elseif(preg_match($this->buildRegex($this->advertise), $text) === 1){
            return self::CHAT_AD;
        }else{
            return self::CHAT_NONE;
        }
    }

    public function buildRegex(string $words) : string{
        return sprintf(self::REGEX, $words);
    }
}