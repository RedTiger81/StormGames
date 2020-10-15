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
 * @date 02 Nisan 2020
 */
declare(strict_types=1);

namespace StormGames\SGCore\inventory;

use pocketmine\inventory\BlockInventory;
use pocketmine\world\Position;

class BeaconInventory extends BlockInventory{
	public function __construct(Position $holder){
		parent::__construct($holder, 1);
	}
}