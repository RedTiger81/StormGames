<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;
use StormGames\Form\WarpForm;
use StormGames\SGCore\utils\PositionUtils;

class WarpCommand extends RDCommand{
	public const WARP = 0;
	public const CATEGORY = 1;

	/** @var array */
	public static $warps = [];
	/** @var array */
	public static $aliases = [];

	public const DENIED = ['type', 'icon', 'alias'];

	public function __construct(string $name){
		self::reload();
		SGCore::getAPI()->getLogger()->info('Warps > §7Warplar yüklendi!');
		parent::__construct($name, 'warp', null, ['w', 'yer', 'mekan']);
	}

	public static function reload(): void{
		$warps = (new Config(self::getConfigPath(), Config::YAML))->getAll();
		foreach($warps as $warpName => $value){
			if($value['type'] === self::CATEGORY){
				foreach($value as $vName => $vValue){
					if(is_array($vValue)){
						self::$warps[$warpName][$vName] = self::decode($vValue);
					}else{
						self::$warps[$warpName][$vName] = $vValue;
					}
					if($vName !== 'type'){
						self::$aliases[$vName] = PositionUtils::decodeString($vValue['loc']);
					}
				}
			}else{
				self::$warps[$warpName] = self::decode($value);
				self::$aliases[$warpName] = PositionUtils::decodeString($value['loc']);
			}
		}
	}
	private static function decode(array $value) : array{
		$array = [
			'icon' => $value['icon'] ?? null,
			'loc' => PositionUtils::decodeString($value['loc'])
		];
		if(isset($value['type'])){
			$array['type'] = $value['type'];
		}
		if(isset($value['alias'])){
			$array['alias'] = $value['alias'];
			if(is_array($value['alias'])){
				foreach($value["alias"] as $alias){
					self::$aliases[$alias] = $array['loc'];
				}
			}else{
				self::$aliases[$value['alias']] = $array['loc'];
			}
		}

		return $array;
	}

	private static function getConfigPath() : string{
		return SGCore::getAPI()->getDataFolder() . 'warps.yml';
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!($sender instanceof SGPlayer)){
			return true;
		}

		if(isset($args[0])){
			if($sender->isOwner()){
				switch($args[0]){
					case 'set':
						if(!empty($args[1])){
							$pos = $sender->getLocation();
							if(!empty($args[2])){
								self::$warps[$args[1]]['type'] = self::CATEGORY;
								self::$warps[$args[1]][$args[2]]['loc'] = $pos;
							}else{
								self::$warps[$args[1]] = ['type' => self::WARP, 'loc' => $pos];
							}
							$sender->sendMessage('§8» §aWarp ayarlandı. Kaydetmek için /w save kullanın');
						}else{
							$sender->sendMessage('§8» §cWarp ismi belirtin');
						}
						return true;
					case 'delete':
						if(!empty($args[1])){
							if(!empty($args[2])){
								unset(self::$warps[$args[1]][$args[2]]);
							}else{
								unset(self::$warps[$args[1]]);
							}
							$sender->sendMessage('§8» §aWarp silindi');
						}else{
							$sender->sendMessage('§8» §cWarp ismi belirtin');
						}
						return true;
					case 'save':
						$warps = [];
						foreach(self::$warps as $cOrW => $value){
							if($value['type'] === self::CATEGORY){
								foreach($value as $wName => $wValue){
									if(isset($wValue['loc'])){
										$wValue['loc'] = PositionUtils::encodeLocation($wValue['loc']);
									}

									$warps[$cOrW][$wName] = $wValue;
								}
							}else{
								$value['loc'] = PositionUtils::encodeLocation($value['loc']);
								$warps[$cOrW] = $value;
							}
						}
						$content = yaml_emit($warps, YAML_UTF8_ENCODING);
						file_put_contents(self::getConfigPath(), $content);
						$sender->sendMessage('§8» §aWarp kaydedildi');
						return true;
					default:
						if(isset(self::$aliases[$args[0]])){
							$sender->teleport(self::$aliases[$args[0]]);
							return true;
						}
						break;
				}
			}else{
				if(isset(self::$aliases[$args[0]])){
					$sender->teleport(self::$aliases[$args[0]]);
					return true;
				}
			}
		}

		$sender->sendForm(new WarpForm($sender));

		return true;
	}

}