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
 * @date 21 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\utils;

use pocketmine\Server;
use StormGames\SGCore\SGPlayer;

class SkyUtils{
	/**
	 * @param SGPlayer  $sender
	 * @param callable $geri
	 * @param callable $ana
	 *
	 * @return array|null
	 */
	public static function getOnlinePlayers(SGPlayer $sender, callable $geri = null, callable $ana = null): ?array{
		$players = [];
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$players[] = $player->getName();
		}
		unset($players[array_search($sender->getName(), $players)]);
		asort($players);
		if(empty($players)){
			$sender->sendAlert("Hata...", "§4Sunucuda şuan senden başka kimse yok :(", "Tekrar Dene", "Kapat", $geri, $ana);
			return null;
		}
		return array_values($players);
	}
}