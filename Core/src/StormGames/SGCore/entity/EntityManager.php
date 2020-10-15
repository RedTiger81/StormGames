<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\entity;

use pocketmine\entity\EntityFactory;
use StormGames\SGCore\entity\decoration\PlayerHead;
use StormGames\SGCore\entity\furniture\Table;
use StormGames\SGCore\entity\projectile\ExplodingBlock;
use StormGames\SGCore\entity\projectile\SmallFireball;
use StormGames\SGCore\entity\projectile\ThrowableBlock;

class EntityManager{

	public static function init() : void{
		EntityFactory::register(Crate::class, ["Kasa"]);
		EntityFactory::register(FloatingText::class, ["Uçan Yazı"]);
		EntityFactory::register(RDHuman::class,  ["Adam"]);
		EntityFactory::register(TransferHuman::class,  ["TransferAdam"]);
		EntityFactory::register(CommandHuman::class,  ["CommandHuman"]);
		EntityFactory::register(PlayerHead::class, ["Kafa"]);
		EntityFactory::register(Table::class, ["Masa"]);
		EntityFactory::register(ThrowableBlock::class, ["Fırlatılan Blok"]);
		EntityFactory::register(ExplodingBlock::class, ["Patlayan Blok"]);
		// Eklenince silinecek
		EntityFactory::register(SmallFireball::class, ["Küçük Ateş Topu"]);
		EntityFactory::register(Lightning::class, ["Yıldırım"]);
        EntityFactory::register(FireworkRocket::class, ["Fişek"]);
	}

}