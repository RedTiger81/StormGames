<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\island;

use Eren5960\SkyBlock\pass\Pass;
use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\player\Player;
use pocketmine\Server;
use function array_search;

class Member{
    /** @var string */
    public $name;
    /** @var string[] */
    public $pass = [];

    public function __construct(string $name, array $pass = []){
        $this->name = $name;
        $this->pass = $pass;
    }

    /**
     * @return mixed
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @return array
     */
    public function getPass(): array{
        return $this->pass;
    }

    /**
     * @param Pass $pass
     * @return bool
     */
    public function hasPass(Pass $pass): bool{
        return array_search($pass->getPermission(), $this->pass) !== false;
    }

	public function addPass(Pass $pass): void{
		$this->pass[] = $pass->getPermission();
	}

	public function removePass(Pass $pass): bool{
		if($this->hasPass($pass)){
			unset($this->pass[array_search($pass->getPermission(), $this->pass)]);
			return true;
		}
		return false;
	}

	public function isOnline(): bool{
    	return self::getPlayer() instanceof Player;
	}

	public function getPlayer(): ?SkyPlayer{
    	return Server::getInstance()->getPlayerExact($this->name);
	}
}