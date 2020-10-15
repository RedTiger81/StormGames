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
 * @date 03 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\SkyBlock\forms;

use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\SkyBlock;
use jojoe77777\FormAPI\ModalForm;
use StormGames\SGCore\SGPlayer;

class IslandCreateModalWindow extends ModalForm{
	public $island = "Normal Ada";
	public $xp_cost = 0;
	public $level_cost = 0;
	public $money_cost = 0;

	public function __construct(){
		parent::__construct(function (SGPlayer $player, ?bool $response){
			if($response === null) return;
			if($response){
				$xp = $player->getXpManager()->getXpLevel();
				$level = SkyBlock::getPlayerIslandLevel($player);
				$money = $player->getMoney();
				if($xp >= $this->xp_cost && $level >= $this->level_cost && $money >= $this->money_cost){
					$player->getXpManager()->subtractXpLevels($this->xp_cost);
					$player->reduceMoney($this->money_cost);
					$player->sendMessage("§8» §aAdan oluşturuluyor. Bu birkaç saniye sürebilir...");
					$island = IslandManager::initIsland($player, $this->island);
					$island->teleport($player);
				}else{
					$info = new InfoForm("§cGereksinimler karşılanmıyor", "§cTüm gereksinimlerin karşılandığından emin olun!", "< Geri dön", "Kapat", function($player){Forms::islandCreate($player);}, null);
					$info->sendToPlayer($player);
				}
			}else{
				Forms::islandCreate($player);
			}
		});
	}
}