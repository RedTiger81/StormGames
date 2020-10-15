<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\block\BlockLegacyIds;

class BlockUtils{
	private const JAVA_TO_BEDROCK = [
		95 => [BlockLegacyIds::STAINED_GLASS],
		126 => [BlockLegacyIds::WOODEN_SLAB],
		125 => [BlockLegacyIds::DOUBLE_WOODEN_SLAB],
		188 => [BlockLegacyIds::FENCE, 0],
		189 => [BlockLegacyIds::FENCE, 1],
		190 => [BlockLegacyIds::FENCE, 2],
		191 => [BlockLegacyIds::FENCE, 3],
		192 => [BlockLegacyIds::FENCE, 4],
		193 => [BlockLegacyIds::FENCE, 5],

		158 => [BlockLegacyIds::WOODEN_SLAB, 0],
		166 => [BlockLegacyIds::INVISIBLE_BEDROCK, 0],
		144 => [0, 0],
		208 => [BlockLegacyIds::GRASS_PATH, 0],
		198 => [BlockLegacyIds::END_ROD, 0],
		199 => [BlockLegacyIds::CHORUS_PLANT, 0],
		251 => [BlockLegacyIds::CONCRETE, 0],
		202 => [BlockLegacyIds::PURPUR_BLOCK, 0],
		204 => [BlockLegacyIds::PURPUR_BLOCK, 0]//,
		//BlockLegacyIds::BEDROCK => [0, 0]
	];

	public static function get(int $id) : array{
		return self::JAVA_TO_BEDROCK[$id] ?? [$id];
	}

	public static function fixId(int $id) : int{
		return self::JAVA_TO_BEDROCK[$id][0] ?? $id;
	}

	public static function fixMeta(int $id, int $meta = 0) : int{
		return self::JAVA_TO_BEDROCK[$id][1] ?? $meta;
	}
}