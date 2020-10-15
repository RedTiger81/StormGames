<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use StormGames\Prefix;
use StormGames\SGCore\entity\CommandHuman;
use StormGames\SGCore\entity\Crate;
use StormGames\SGCore\entity\decoration\PlayerHead;
use StormGames\SGCore\entity\furniture\Table;
use StormGames\SGCore\entity\RDHuman;
use StormGames\SGCore\entity\TransferHuman;
use StormGames\SGCore\entity\utils\Skins;
use StormGames\SGCore\permission\DefaultPermissions;
use StormGames\SGCore\SGCore;
use StormGames\SGCore\SGPlayer;
use StormGames\SGCore\utils\TextUtils;

class NPCCommand extends Command{
	/** @var \Closure */
	public static $npcExtraArg = [];

	public function __construct(string $name){
		parent::__construct($name, "NPC oluşturur");

		$this->setPermission(DefaultPermissions::ADMIN);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof SGPlayer)){
			return true;
		}

		if(count($args) <= 0){
			throw new InvalidCommandSyntaxException();
		}

		$name = $args[0];
		switch($args[0]){
			case "behmut":
				$nbt = EntityFactory::createBaseNBT($sender->getPosition()->add(0, 2, 0), null, (float) ($args[1] ?? $sender->getLocation()->yaw), (float) ($args[2] ?? $sender->getLocation()->pitch));
				$nbt->setTag('Skin', (new CompoundTag())->setString('Name', 'behmut')->setByteArray('Data', str_repeat('0', 8192)));
				$entity = new RDHuman($sender->getWorld(), $nbt);
				$entity->setSkin(Skins::getSkin("behmut", Skins::MODEL_WHALE));
				$entity->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SWIMMING, true);
				$entity->spawnToAll();
				break;
			case "masa":
				$nbt = EntityFactory::createBaseNBT($sender->getPosition(), null, $sender->getLocation()->yaw);
				$entity = new Table($sender->getWorld(), $nbt);
				$entity->spawnToAll();
				break;
			case "kafa":
				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				$nbt = EntityFactory::createBaseNBT($sender->getPosition(), null, $sender->getLocation()->yaw);
				$nbt->setString("Head", "chest"); // HACK!
				$entity = new PlayerHead($sender->getWorld(), $nbt);
				$entity->setHead($args[1], $sender);
				$entity->spawnToAll();
				break;
			case "kasa":
				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				$nbt = EntityFactory::createBaseNBT($sender->getPosition()->floor()->add(0.5, 0, 0.5));
				$nbt->setString("CrateTier", $args[1]);
				$entity = new Crate($sender->getWorld(), $nbt);
				$entity->spawnToAll();
				break;
			case "transfer":
				if(count($args) < 2){
					throw new InvalidCommandSyntaxException();
				}

				$nbt = EntityFactory::createBaseNBT($sender, null, $sender->getLocation()->yaw, $sender->getLocation()->pitch);
				$nbt->setString("Transfer", $args[1]);
				$nbt->setTag('Skin', (new CompoundTag())->setString('Name', 'behmut')->setByteArray('Data', str_repeat('0', 8192)));
				$entity = new TransferHuman($sender->getWorld(), $nbt);
				$entity->setSkin($sender->getSkin());
				$entity->setNameTag($args[2]);
				$entity->spawnToAll();
				break;
			case "command":
				if(empty($args[3])){
					$sender->sendMessage("/npc command isim skin komut devamı fln");
					$sender->sendMessage("/npc command \"isim %line% &esarı alt\" arena w a");
					break;
				}
				$isim = $args[1];
				$skinFile = $args[2] . ".png";
				unset($args[0], $args[1], $args[2]);
				@mkdir($path = SGCore::getAPI()->getDataFolder() . "npc_skins" . DIRECTORY_SEPARATOR);
				$nbt = EntityFactory::createBaseNBT($sender->getLocation(), null, $sender->getLocation()->yaw, $sender->getLocation()->pitch);
				$nbt->setString("command", implode(" ", $args));
				$nbt->setTag('Skin', (new CompoundTag())->setString('Name', 'normal')->setByteArray('Data', str_repeat('0', 8192)));
				$entity = new CommandHuman($sender->getWorld(), $nbt);
				$entity->setSkin(new Skin($sender->getSkin()->getSkinId(), Skins::fromFile($path. $skinFile), "", $sender->getSkin()->getGeometryData()));
				$entity->setNameTag(str_replace(["%line%", "&"], [TextFormat::EOL, TextFormat::ESCAPE], $isim));
				$entity->spawnToAll();
				break;
			case "kill":
				$count = 0;
				$saveId = $args[1] ?? null;
				foreach($sender->getWorld()->getEntities() as $entity){
					if(!($entity instanceof Player) and $saveId === TextUtils::classStringToName(get_class($entity))){
						$entity->close();
						$count++;
					}
				}
				$sender->sendMessage(Prefix::MAIN . $count . ' entity öldürdünüz!');
				return true;
			default:
				if(isset(self::$npcExtraArg[$args[0]])){
					(self::$npcExtraArg[$args[0]])($sender, $args);
				}else{
					$sender->sendMessage(Prefix::MAIN . "$name isimli entity yok!");
				}
				return true;
		}

		$sender->sendMessage(Prefix::MAIN . ucfirst($name) . " isimli entity oluşturuldu!");

		return true;
	}
}