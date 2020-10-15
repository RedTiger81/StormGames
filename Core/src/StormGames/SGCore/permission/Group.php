<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\permission;

use StormGames\SGCore\SGPlayer;

class Group{
    /** @var string */
    protected $name;
    /** @var string[] */
    protected $permissions;
    /** @var string */
    protected $format, $chatFormat;
    /** @var bool */
    protected $isDefault = false;
    /** @var int */
    protected $priority;
    /** @var int */
    protected $time = 0;

    public function __construct(string $name, array $permissions, int $priority, ?string $chat = null, string $format = ''){
        $this->name = $name;
        $this->permissions = $permissions;
        $this->format = $format;
        $this->chatFormat = $chat ?? "%s §8> §7%s";
        $this->isDefault = GroupManager::getDefaultGroup() === $name;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getPermissions() : array{
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getFormat() : string{
        return $this->format;
    }

    /**
     * @return string
     */
    public function getChatFormat() : string{
        return $this->chatFormat;
    }

    /**
     * @return bool
     */
    public function isDefault() : bool{
        return $this->isDefault;
    }

    /**
     * @return int
     */
    public function getPriority() : int{
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getTime() : int{
        return $this->time;
    }

    /**
     * @param int $time
     * @return Group
     */
    public function setTime(int $time) : self{
        $this->time = $this->isDefault ? 0 : $time;

        return $this;
    }

    public function convertChatFormat(SGPlayer $player, string $text) : string{
        return sprintf($this->getChatFormat(), $player->getNameTag(), $text);
    }

    public function equals(Group $other) : bool{
        return $this->name === $other->getName();
    }
}