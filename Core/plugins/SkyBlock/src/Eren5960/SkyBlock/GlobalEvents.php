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
 
namespace Eren5960\SkyBlock;
 
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;

class GlobalEvents implements Listener{
    public function  onPlayerCreation(PlayerCreationEvent $event){
    	$event->setPlayerClass(SkyPlayer::class);
    }
}