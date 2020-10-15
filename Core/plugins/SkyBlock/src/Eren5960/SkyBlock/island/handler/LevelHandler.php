<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 27 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\island\handler;
 
use Eren5960\SkyBlock\island\Island;
use Eren5960\SkyBlock\island\island\IslandBase;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Liquid;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Sugarcane;

class LevelHandler{
	public static function down(Block $block, IslandBase $island): void{
		$opt = $island->options;
		$xp = 0.0;
		if ($block instanceof Slab) {
			$xp = 0.25;
		}elseif(self::isTrueType($block)){
			$xp = 0.5;
		}
		if($xp === 0.0) return;
		$opt->xp = $opt->xp - $xp;
		if ($opt->xp < 0.0) {
			$opt->need_xp = $opt->need_xp - ($opt->need_xp / 10);
			$opt->xp = $opt->need_xp - 1;
			if($opt->level !== 0){
				$opt->level--;
				if($island->getOwner() !== null){
					$island->getOwner()->sendTitle("§cSeviye düştü!", "§7» §fSv. §b" . $opt->level . " §7«");
				}
				foreach ($opt->members as $name => $member) {
					if($member->isOnline()){
						$member->getPlayer()->sendTitle("§cSeviye düştü!", "§7» §fSv. §b" . $opt->level . " §7«");
					}
				}
			}
		}
	}

	public static function up(Block $block, IslandBase $island): void{
		$opt = $island->options;
		$xp = 0.0;
		if ($block instanceof Stair) {
			$xp = 0.25;
		}elseif(self::isTrueType($block)){
			$xp = 0.5;
		}
		if($xp === 0.0) return;
		$opt->xp = $opt->xp + $xp;
		if ($opt->xp >= $opt->need_xp) {
			$opt->xp = 0;
			$opt->need_xp = ($opt->need_xp / 10) + $opt->need_xp;
			$opt->level++;
			if($island->getOwner() !== null){
				$island->getOwner()->sendTitle("§aSeviye atlandı!", "§7» §fSv. §b" . $opt->level . " §7«");
			}
			foreach ($opt->members as $name => $member) {
				if($member->isOnline()){
					$member->getPlayer()->sendTitle("§aSeviye atlandı!", "§7» §fSv. §b" . $opt->level . " §7«");
				}
			}
		}
	}

	private static function isTrueType(Block $block): bool{
		return !($block instanceof Liquid || $block instanceof Sugarcane);
	}
}