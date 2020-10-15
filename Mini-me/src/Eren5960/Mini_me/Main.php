<?php

declare(strict_types=1);

namespace Eren5960\Mini_me;

use Eren5960\Mini_me\entity\Farmer;
use Eren5960\Mini_me\entity\Miner;
use Eren5960\Mini_me\entity\Woodcutter;
use Eren5960\SkyBlock\SkyPlayer;
use pocketmine\entity\EntityFactory;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
	public const NBTP = 'mini_me_';

	public const ENTITIES = [
		"madenci" => Miner::class,
		"oduncu" => Woodcutter::class,
		"ciftci" => Farmer::class
	];

	public const NAMES = [
		"madenci" => "§3Madenci Çırak",
		"oduncu" => "§6Oduncu Çırak",
		"ciftci" => "§2Çiftçi Çırak"
	];

	public function onEnable() : void{
		$this->getServer()->getCommandMap()->register('mme', new MCmd());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		foreach(self::ENTITIES as $name => $class){
			EntityFactory::register($class, [$name]);
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		/** @var SkyPlayer $player */
		$player = $event->getPlayer();
		$item = $event->getItem();
		$tag = $item->getNamedTag();

		if($tag->hasTag(self::NBTP . 'type', StringTag::class) && $tag->hasTag(self::NBTP . 'owner', StringTag::class)){
			$event->setCancelled();
			$owner = $tag->getTagValue(self::NBTP . 'owner', StringTag::class);

			if($player->getName() !== $owner){
				$player->sendPopup("§cYalnızca sahibi yerleştirebilir.");
				return;
			}
			$player->getInventory()->removeItem($item->pop());

			$type = $tag->getTagValue(self::NBTP . 'type', StringTag::class);

			$nbt = EntityFactory::createBaseNBT($event->getBlock()->getPos()->add(0.4, 0, -0.3));
			$nbt->setString(self::NBTP . "owner", $owner);
			$nbt->setInt(self::NBTP . 'created', $tag->getInt(Main::NBTP . 'created', time()));
			$nbt->setInt(self::NBTP . 'level', $tag->getInt(Main::NBTP . 'level', 1));
			$nbt->setString(self::NBTP . 'type', $type);

			if($tag->hasTag(self::NBTP . 'Inventory', ListTag::class)){
				$nbt->setTag('Inventory', $tag->getListTag(self::NBTP . 'Inventory'));
			}

			$nbt->setTag('Skin', $player->getSkinTag());

			$entity = EntityFactory::create(self::ENTITIES[$type], $player->getWorld(), $nbt);
			$entity->spawnToAll();
		}
	}
}
