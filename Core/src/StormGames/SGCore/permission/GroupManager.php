<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\permission;

use StormGames\SGCore\SGCore;
use function substr;
use function array_keys;
use function json_decode;
use function file_get_contents;


class GroupManager{
    /** @var string */
    protected static $defaultGroup;
    /** @var Group[] */
    protected static $groups = [];

    public static function init() : void{
        $groups = json_decode(file_get_contents(SGCore::getAPI()->getResourcesDir() . "groups.json"), true);

        self::$defaultGroup = $groups["default"];
        foreach($groups as $name => $group){
            if($name === "default") continue;

            self::registerGroup(new Group($name, self::convertPermissions($group["permissions"]), $group["priority"] ?? 0, $group["chat_format"] ?? null, $group["format"] ?? ""));
        }
    }

    public static function convertPermissions(array $permissions) : array{
        $result = [];
        foreach($permissions as $permission){
            $positive = substr($permission, 0, 1) !== "-";
            $result[$positive ? $permission : substr($permission, 1)] = $positive;
        }

        return $result;
    }

    /**
     * Grup kaydedir
     *
     * @param Group $group
     */
    public static function registerGroup(Group $group) : void{
        self::$groups[$group->getName()] = $group;
    }

    /**
     * @param string $groupName
     * @return Group|null
     */
    public static function getGroup(string $groupName) : ?Group{
        return self::$groups[$groupName] ?? null;
    }

    /**
     * @return string
     */
    public static function getDefaultGroup() : string{
        return self::$defaultGroup;
    }

    /**
     * @return array
     */
    public static function getGroupsList() : array{
        return array_keys(self::$groups);
    }
}