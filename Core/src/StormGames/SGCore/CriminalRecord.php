<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore;

use pocketmine\utils\TextFormat;
use StormGames\SGCore\utils\TextUtils;

class CriminalRecord{

    /** @var bool */
    private $banned = true;
    /** @var string */
    private $banReason;
    /** @var int */
    private $banTimestamp;
    /** @var string */
    private $bannedBy;
    /** @var int */
    private $banCount, $kickCount;
    /** @var SGPlayer */
    private $player;

    public function __construct(SGPlayer $player, bool $banned, string $banReason, int $banTimestamp, string $bannedBy, int $banCount, int $kickCount){
        $this->player = $player;
        $this->banned = $banned;
        $this->banReason = $banReason;
        $this->banTimestamp = $banTimestamp;
        $this->bannedBy = $bannedBy;
        $this->banCount = $banCount;
        $this->kickCount = $kickCount;
    }

    public function check() : void{
        $diff = $this->banTimestamp - time();
        if($diff <= 0){
            $this->banned = false;
            $this->banTimestamp = 0;
        }
    }

    public function getBanMessage() : string{
        $banTime = new \DateTime();
        $banTime->setTimestamp($this->banTimestamp);
        $time = new \DateTime();
        $diff = $banTime->diff($time);
        return TextFormat::RED . TextUtils::center($this->player->translate('ban.banned', [
            TextFormat::DARK_RED . $this->player->translate('ban.reason.' . $this->player->getCriminalRecord()->getBanReason()) . TextFormat::RED,
            TextFormat::DARK_RED . $diff->format($this->player->translate('ban.time.format')) . TextFormat::RED,
        ]));
    }

    /**
     * @return bool
     */
    public function isBanned() : bool{
        return $this->banned;
    }

    /**
     * @param bool $banned
     * @param string $reason
     * @param int $timeStamp
     * @param string $bannedBy
     */
    public function setBanned(bool $banned, string $reason, int $timeStamp, string $bannedBy) : void{
        $this->banned = $banned;
        $this->banReason = $reason;
        $this->banCount += 1;
        $this->banTimestamp = $timeStamp;
        $this->bannedBy = $bannedBy;
        $this->player->disconnect('', $this->getBanMessage());
    }

    public function kick(string $reason = ''){
        $this->kickCount += 1;
        $this->player->disconnect('', TextUtils::center($this->player->translate('kick.kicked', [$reason]))); // TODO
    }

    /**
     * @return string
     */
    public function getBanReason() : string{
        return $this->banReason;
    }

    /**
     * @return int
     */
    public function getBanTimestamp() : int{
        return $this->banTimestamp;
    }

    /**
     * @return string
     */
    public function getBannedBy() : string{
        return $this->bannedBy;
    }

    /**
     * @return int
     */
    public function getBanCount() : int{
        return $this->banCount;
    }

    /**
     * @return int
     */
    public function getKickCount() : int{
        return $this->kickCount;
    }
}