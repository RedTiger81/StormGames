<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\permission;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use StormGames\SGCore\entity\utils\Skins;

class DefaultPermissions{
    public const SEPARATOR = '.';

    public const ROOT = 'stormgames' . self::SEPARATOR;
    public const ROOT_CAPE = self::ROOT . 'cape' . self::SEPARATOR;
    public const ROOT_COMMAND = self::ROOT . 'command' . self::SEPARATOR;

    public const ADMIN = self::ROOT . 'admin';
    public const MODERATOR = self::ROOT . 'moderator';

    public const CHAT_BYPASS = self::ROOT . 'chat' . self::SEPARATOR . 'bypass';
	public const CHAT_USE_COLORS = self::ROOT . 'chat' . self::SEPARATOR . 'useColors';
	public const MUSIC_VOTE_BYPASS = self::ROOT . 'music' . self::SEPARATOR . 'bypass';

	public const VIP = self::ROOT . 'vip';
    public const VIP_PLUS = self::ROOT . 'vip+';
    public const MVP = self::ROOT . 'mvp';
    public const MVP_PLUS = self::ROOT . 'mvp+';

    public const FAMOUS = self::ROOT . 'famous';

    public static function registerPermission(Permission $permission, Permission $parent = null) : ?Permission{
        if($parent instanceof Permission){
	        $parent->getChildren()[$permission->getName()] = true;

        	return self::registerPermission($permission);
        }
        $permManager = PermissionManager::getInstance();
        $permManager->addPermission($permission);

        return $permManager->getPermission($permission->getName());
    }

    public static function loadDefaultPermissions() : void{
        $admin = self::registerPermission(new Permission(self::ADMIN, null, Permission::DEFAULT_OP));

        // Ranks
	    $mvpPlus = self::registerPermission(new Permission(self::MVP_PLUS, null, Permission::DEFAULT_FALSE), $admin);
	    $mvp = self::registerPermission(new Permission(self::MVP, null, Permission::DEFAULT_FALSE), $mvpPlus);
	    $vipPlus = self::registerPermission(new Permission(self::VIP_PLUS, null, Permission::DEFAULT_FALSE), $mvp);
	    self::registerPermission(new Permission(self::VIP, null, Permission::DEFAULT_FALSE), $vipPlus);
	    $vipPlus->recalculatePermissibles();
	    $mvp->recalculatePermissibles();
	    $mvpPlus->recalculatePermissibles();

	    self::registerPermission(new Permission(self::FAMOUS, null, Permission::DEFAULT_FALSE), $admin);

        // Capes
        $capes = self::registerPermission(new Permission(self::ROOT_CAPE . 'all', null, Permission::DEFAULT_OP), $admin);
        foreach(Skins::getCapes() as $name => $cape){
            self::registerPermission(new Permission(self::ROOT_CAPE . $name, null, Permission::DEFAULT_FALSE), $capes);
        }

        $mod = self::registerPermission(new Permission(self::ROOT . '.moderator', 'Moderator main permission', Permission::DEFAULT_FALSE), $admin);
        $mod->recalculatePermissibles();

        $commands = self::registerPermission(new Permission(self::ROOT_COMMAND . '*', 'All commands permission', Permission::DEFAULT_FALSE), $admin);
        self::registerPermission(new Permission(self::ROOT_COMMAND . 'broadcast', 'Broadcast command permission', Permission::DEFAULT_FALSE), $commands);
        $commands->recalculatePermissibles();

        self::registerPermission(new Permission(self::CHAT_BYPASS, null, Permission::DEFAULT_OP), $admin);
        self::registerPermission(new Permission(self::CHAT_USE_COLORS, null, Permission::DEFAULT_OP), $admin);
        self::registerPermission(new Permission(self::MUSIC_VOTE_BYPASS, null, Permission::DEFAULT_OP), $admin);
        $admin->recalculatePermissibles();
    }
}