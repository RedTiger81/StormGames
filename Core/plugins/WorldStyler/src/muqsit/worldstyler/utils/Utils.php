<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\world\World;
use pocketmine\item\ItemFactory;

class Utils {

    const FILESIZES = 'BKMGTP';

    public static function humanFilesize(string $file, int $decimals = 2) : string
    {
        //from https://stackoverflow.com/questions/15188033/human-readable-file-size but customized a bit
        $bytes = (string) filesize($file);
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . (self::FILESIZES[$factor] ?? "");
    }

    public static function updateChunks(World $world, int $minChunkX, int $maxChunkX, int $minChunkZ, int $maxChunkZ) : void
    {
        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunk = $world->getChunk($chunkX, $chunkZ);
                //$chunk->setChanged(true);
                $world->setChunk($chunkX, $chunkZ, $chunk, false);
            }
        }
    }

    public static function getBlockFromString(string $block) : ?Block
    {
        try {
            return ItemFactory::fromString($block)->getBlock();
        } catch (\InvalidArgumentException $e) {
            $data = explode(":", $block, 3);
            return BlockFactory::get((int) $data[0], (int) ($data[1] ?? 0));
        }
    }

    public static function getPCMapping() : BlockToBlockMapping
    {
        $mapping = new BlockToBlockMapping();

        for ($meta = 0; $meta < 16; ++$meta) {
            $mapping->add(BlockFactory::get(BlockLegacyIds::ACTIVATOR_RAIL, $meta), BlockFactory::get(BlockLegacyIds::WOODEN_SLAB, $meta));
            $mapping->add(BlockFactory::get(BlockLegacyIds::INVISIBLE_BEDROCK, $meta), BlockFactory::get(BlockLegacyIds::STAINED_GLASS, $meta));
            $mapping->add(BlockFactory::get(BlockLegacyIds::DROPPER, $meta), BlockFactory::get(BlockLegacyIds::DOUBLE_WOODEN_SLAB, $meta));
            $mapping->add(BlockFactory::get(BlockLegacyIds::REPEATING_COMMAND_BLOCK, $meta), BlockFactory::get(BlockLegacyIds::FENCE, TreeType::SPRUCE()->getMagicNumber()));
            $mapping->add(BlockFactory::get(BlockLegacyIds::CHAIN_COMMAND_BLOCK, $meta), BlockFactory::get(BlockLegacyIds::FENCE, TreeType::BIRCH()->getMagicNumber()));
            $mapping->add(BlockFactory::get(BlockLegacyIds::HARD_GLASS_PANE, $meta), BlockFactory::get(BlockLegacyIds::FENCE, TreeType::JUNGLE()->getMagicNumber()));
            $mapping->add(BlockFactory::get(BlockLegacyIds::HARD_STAINED_GLASS_PANE, $meta), BlockFactory::get(BlockLegacyIds::FENCE, TreeType::DARK_OAK()->getMagicNumber()));
            $mapping->add(BlockFactory::get(BlockLegacyIds::CHEMICAL_HEAT, $meta), BlockFactory::get(BlockLegacyIds::FENCE, TreeType::ACACIA()->getMagicNumber()));
            $mapping->add(BlockFactory::get(BlockLegacyIds::GLOW_STICK, $meta), BlockFactory::get(BlockLegacyIds::BARRIER, $meta));
        }

        $mapping->add(BlockFactory::get(BlockLegacyIds::DOUBLE_STONE_SLAB, 6), BlockFactory::get(BlockLegacyIds::DOUBLE_STONE_SLAB, 7));
        $mapping->add(BlockFactory::get(BlockLegacyIds::DOUBLE_STONE_SLAB, 7), BlockFactory::get(BlockLegacyIds::DOUBLE_STONE_SLAB, 6));

        $mapping->add(BlockFactory::get(BlockLegacyIds::STONE_SLAB, 6), BlockFactory::get(BlockLegacyIds::STONE_SLAB, 7));
        $mapping->add(BlockFactory::get(BlockLegacyIds::STONE_SLAB, 7), BlockFactory::get(BlockLegacyIds::STONE_SLAB, 6));
        $mapping->add(BlockFactory::get(BlockLegacyIds::STONE_SLAB, 15), BlockFactory::get(BlockLegacyIds::STONE_SLAB, 14));

        foreach ([BlockLegacyIds::TRAPDOOR, BlockLegacyIds::IRON_TRAPDOOR] as $blockId) {
            $mapping->add(BlockFactory::get($blockId, 0), BlockFactory::get($blockId, 3));
            $mapping->add(BlockFactory::get($blockId, 1), BlockFactory::get($blockId, 2));
            $mapping->add(BlockFactory::get($blockId, 2), BlockFactory::get($blockId, 1));
            $mapping->add(BlockFactory::get($blockId, 3), BlockFactory::get($blockId, 0));
            $mapping->add(BlockFactory::get($blockId, 4), BlockFactory::get($blockId, 7));
            $mapping->add(BlockFactory::get($blockId, 5), BlockFactory::get($blockId, 6));
            $mapping->add(BlockFactory::get($blockId, 6), BlockFactory::get($blockId, 5));
            $mapping->add(BlockFactory::get($blockId, 7), BlockFactory::get($blockId, 4));
            $mapping->add(BlockFactory::get($blockId, 8), BlockFactory::get($blockId, 11));
            $mapping->add(BlockFactory::get($blockId, 9), BlockFactory::get($blockId, 10));
            $mapping->add(BlockFactory::get($blockId, 10), BlockFactory::get($blockId, 9));
            $mapping->add(BlockFactory::get($blockId, 11), BlockFactory::get($blockId, 8));
            $mapping->add(BlockFactory::get($blockId, 12), BlockFactory::get($blockId, 15));
            $mapping->add(BlockFactory::get($blockId, 13), BlockFactory::get($blockId, 14));
            $mapping->add(BlockFactory::get($blockId, 14), BlockFactory::get($blockId, 13));
            $mapping->add(BlockFactory::get($blockId, 15), BlockFactory::get($blockId, 12));
        }

        return $mapping;
    }
}
