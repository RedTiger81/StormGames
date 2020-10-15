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
 
namespace Eren5960\SkyBlock\pass;
 
class Pass{
	public const PLACE_BLOCK = "place.block";
	public const BREAK_BLOCK = "break.block";
	public const OPEN_CONTAINER = "open.container";
	public const MOB_ACTION = "action.mob";







	/** @var string */
	private $name;
	/** @var string */
	private $permission;

	public function __construct(string $name, string $permission){
		$this->name = $name;
		$this->permission = $permission;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPermission() : string{
		return $this->permission;
	}
}