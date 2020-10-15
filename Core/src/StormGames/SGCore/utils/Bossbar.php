<?php
/**
 *  Copyright (C) StormGames, Inc - All Rights Reserved
 *  Unauthorized copying of this file, via any medium is strictly prohibited
 *  Proprietary and confidential
 *  Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, July 2019
 */
declare(strict_types=1);

namespace StormGames\SGCore\utils;

use pocketmine\entity\Attribute;
use pocketmine\entity\EntityFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataTypes;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use StormGames\SGCore\SGPlayer;

class Bossbar{
    private const ENTITY_ID = EntityLegacyIds::SLIME;

    /** @var int */
    private $eid;
    /** @var mixed[] */
    private $metadata;
    /** @var SGPlayer[] */
    private $viewers = [];
    /** @var int */
    private $health, $maxHealth;

    public function __construct(string $title, int $health = 1, int $maxHealth = 1){
        $this->eid = EntityFactory::nextRuntimeId();
	    $this->metadata = new EntityMetadataCollection();
	    $this->metadata->setLong(EntityMetadataProperties::FLAGS, 0
		    ^ 1 << EntityMetadataFlags::SILENT
		    ^ 1 << EntityMetadataFlags::INVISIBLE
		    ^ 1 << EntityMetadataFlags::NO_AI
		    ^ 1 << EntityMetadataFlags::FIRE_IMMUNE);
	    $this->metadata->setShort(EntityMetadataProperties::MAX_AIR, 400);
	    $this->metadata->setString(EntityMetadataProperties::NAMETAG, $title);
	    $this->metadata->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
	    $this->metadata->setFloat(EntityMetadataProperties::SCALE, 0);
	    $this->metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
	    $this->metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
        $this->setHealth($health, false);
        $this->setMaxHealth($maxHealth, false);
    }

    /**
     * @param SGPlayer|SGPlayer[] $player
     */
    public function sendTo($player) : void{
        $add = new AddActorPacket();
        $add->entityRuntimeId = $this->eid;
        $add->type = self::ENTITY_ID;
        $add->metadata = [$this->metadata];
        $add->position = new Vector3();

        $health = $this->getHealthPacket();

        $boss = new BossEventPacket();
        $boss->bossEid = $this->eid;
        $boss->eventType = BossEventPacket::TYPE_SHOW;
        $boss->title = $this->getTitle();
        $boss->healthPercent = $this->health;
        $boss->color = $boss->overlay = $boss->unknownShort = 0;

        foreach(Utils::broadcastPacket($player, [$add, $health, $boss]) as $p){
            $this->viewers[$p->getId()] = $p;
        }
    }

    /**
     * @param SGPlayer|SGPlayer[] $player
     */
    public function removeFrom($player){
        $boss = new BossEventPacket();
        $boss->bossEid = $this->eid;
        $boss->eventType = BossEventPacket::TYPE_HIDE;

        $remove = new RemoveActorPacket();
        $remove->entityUniqueId = $this->eid;

        foreach(Utils::broadcastPacket($player, [$boss, $remove]) as $p){
            unset($this->viewers[$p->getId()]);
        }
    }

    /**
     * @param SGPlayer|SGPlayer[] $player
     */
    public function updateForPlayers($player){
        $boss = new BossEventPacket();
        $boss->bossEid = $this->eid;
        $boss->eventType = BossEventPacket::TYPE_TITLE;
        $boss->healthPercent = $this->health;
        $boss->title = $this->getTitle();

        $bossHealth = clone $boss;
        $bossHealth->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;

        $health = $this->getHealthPacket();

        $setEntity = new SetActorDataPacket();
        $setEntity->entityRuntimeId = $this->eid;
        $setEntity->metadata = $this->metadata;

        Utils::broadcastPacket($player, [$boss, $bossHealth, $health, $setEntity]);
    }

    public function getHealthPacket() : UpdateAttributesPacket{
        $attr = Attribute::get(Attribute::HEALTH);
        $attr->setMaxValue($this->maxHealth);
        $attr->setValue($this->health);

        $pk = new UpdateAttributesPacket();
        $pk->entityRuntimeId = $this->eid;
        $pk->entries = [$attr];

        return $pk;
    }

    public function getId() : int{
        return $this->eid;
    }

    public function getHealth() : int{
        return $this->health;
    }

    public function setHealth(int $health, bool $send = true) : void{
        $this->health = $health;

        if($this->maxHealth < $this->health){
            $this->maxHealth = $this->health;
        }

        if($send){
            $this->updateForPlayers($this->viewers);
        }
    }

    public function getMaxHealth() : int{
        return $this->maxHealth;
    }

    public function setMaxHealth(int $maxHealth, bool $send = true) : void{
        $this->maxHealth = $maxHealth;

        if($send){
            $this->updateForPlayers($this->viewers);
        }
    }

    public function getTitle() : string{
        return "";
    }

    public function setTitle(string $title, bool $send = true) : void{
        $this->setMetadata(EntityMetadataProperties::NAMETAG, EntityMetadataTypes::STRING, $title, $send);
    }

    /**
     * @param int $metadata
     * @param int $type
     * @param mixed $value
     * @param bool $send
     */
    public function setMetadata(int $metadata, int $type, $value, bool $send = true) : void{
        $this->metadata->setString($metadata, $value);

        if($send){
            $this->updateForPlayers($this->viewers);
        }
    }

    /**
     * @return SGPlayer[]
     */
    public function getViewers() : array{
        return $this->viewers;
    }

}