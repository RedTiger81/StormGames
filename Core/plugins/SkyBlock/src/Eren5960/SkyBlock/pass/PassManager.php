<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\pass;

class PassManager{
    /** @var Pass[] */
    public static $pass = [];

    public static function init(): void{
        self::registerPass(new Pass("Sandık Açma", Pass::OPEN_CONTAINER));
        self::registerPass(new Pass("Blok Kırma", Pass::BREAK_BLOCK));
        self::registerPass(new Pass("Blok Koyma", Pass::PLACE_BLOCK));
	    self::registerPass(new Pass("Hayvanlara Saldırma", Pass::MOB_ACTION));
    }

    private static function registerPass(Pass $pass): void{
        self::$pass[$pass->getPermission()] = $pass;
    }

	public static function getPass(string $perm): ?Pass{
		return self::$pass[$perm] ?? null;
	}

	public static function getPassByName(string $name): ?Pass{
		foreach(self::$pass as $permission => $pass){
			if($pass->getName() === $name){
				return $pass;
			}
		}
    	return null;
	}

	/**
	 * @return string[]
	 */
	public function getPassNames(): array{
        $names = [];
        foreach(self::$pass as $pass) $names[] = $pass->getName();
        return $names;
    }
}