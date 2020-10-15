<?php

/**
 * Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */

declare(strict_types=1);

namespace StormGames;

use pocketmine\utils\TextFormat;
use StormGames\SGCore\SGCore;

/* @method static VOTE()
 * @method static COSMETIC()
 * @method static CHAT()
 * @method static MOD()
 * @method static MUSIC()
 * @method static PETS()
 * @method static ANVIL()
 * @method static ECONOMY()
 * @method static HOME()
 * @method static MESSAGE()
 * @method static MISSION()
 * @method static PLOT()
 * @method static TPA()
 * @method static TRANSLATE()
 * @method static RealEstate()
 * @method static KIT()
 */
class Prefix{
	public const MAIN_COLOR = TextFormat::BLUE;

	public const PURE = TextFormat::DARK_GRAY . '» ' . TextFormat::GRAY;
	public const MAIN = SGCore::SERVER_NAME_FORMAT . TextFormat::DARK_GRAY . ' | ' . TextFormat::GRAY;
	public const SKYBLOCK = TextFormat::RED . 'SkyBlock' . TextFormat::DARK_GRAY . '> ' . TextFormat::GRAY;
	public const FORMAT = self::MAIN_COLOR . '%s' . TextFormat::DARK_GRAY . '> ' . TextFormat::GRAY;
	public const TIP = TextFormat::LIGHT_PURPLE . TextFormat::BOLD . 'TIP' . TextFormat::DARK_GRAY . '> ' . TextFormat::RESET . TextFormat::GRAY;

	public const FORM_TITLE = TextFormat::DARK_GRAY . TextFormat::BOLD . '» ' . TextFormat::RESET . '%s' . TextFormat::DARK_GRAY . TextFormat::BOLD . ' «' . TextFormat::RESET;

	public static function __callStatic($name, $arguments){
		return sprintf(self::FORMAT, $name);
	}
}