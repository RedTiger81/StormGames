<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\plugin;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGCore;

abstract class StormGamesPlugin extends PluginBase{
    public const TABLE_PLAYERS = 'players';
    public const TABLE_SKYBLOCK_MISSIONS = 'skyblockMissions';
    public const TABLE_SKYBLOCK_PLAYERS = 'skyblockPlayers';
    public const TABLE_CRIMINAL_RECORDS = 'criminals';
    public const TABLE_PROMOTIONS = 'promotions';

	public function onEnable(){
		SGCore::getAPI()->getLogger()->info(TextFormat::YELLOW . $this->getDescription()->getName() . " eklentisi başlatılıyor...");
	}

	public function getResourcesDir() : string{
		return $this->getFile() . 'resources' . DIRECTORY_SEPARATOR;
	}
}