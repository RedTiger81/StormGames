<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;


use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket as SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\player\Player;

trait LinkableTrait {
	/** @var Entity|Linkable $link */
	protected $link;

	/**
	 * @return Entity|Linkable|null
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * @param Entity|Linkable|null $entity
	 *
	 * @return Entity|Linkable
	 */
	public function setLink(?Linkable $entity) {
		$this->link = $entity;
		$entity->setLink($this);
		$viewers = $this->getViewers();
		if($entity !== null) {
			$link = new EntityLink();
			$link->fromEntityUniqueId = $this->getId();
			$link->type = EntityLink::TYPE_RIDER;
			$link->toEntityUniqueId = $entity->getId();
			$link->immediate = true;
			if($entity instanceof Player) {
				$pk = new SetEntityLinkPacket();
				$pk->link = $link;
				$entity->getNetworkSession()->sendDataPacket($pk);
				$link_2 = new EntityLink();
				$link_2->fromEntityUniqueId = $entity->getId();
				$link_2->type = EntityLink::TYPE_RIDER;
				$link_2->toEntityUniqueId = 0;
				$link_2->immediate = true;
				$pk = new SetEntityLinkPacket();
				$pk->link = $link_2;
				$entity->getNetworkSession()->sendDataPacket($pk);
				unset($viewers[$entity->getId()]);
			}
		}else{
			$link = new EntityLink();
			$link->fromEntityUniqueId = $this->getId();
			$link->type = EntityLink::TYPE_RIDER;
			$link->toEntityUniqueId = $entity->getId();
			$link->immediate = true;
			if($entity instanceof Player) {
				$pk = new SetEntityLinkPacket();
				$pk->link = $link;
				$entity->getNetworkSession()->sendDataPacket($pk);
				$link_2 = new EntityLink();
				$link_2->fromEntityUniqueId = $entity->getId();
				$link_2->type = EntityLink::TYPE_RIDER;
				$link_2->toEntityUniqueId = 0;
				$link_2->immediate = true;
				$pk = new SetEntityLinkPacket();
				$pk->link = $link_2;
				$entity->getNetworkSession()->sendDataPacket($pk);
				unset($viewers[$entity->getId()]);
			}
		}
		return $this;
	}

	public function unlink() : bool {
		$this->link->setLink(null);
		$this->link = null;

		$viewers = $this->getViewers();
		$entity = $this->link;
		$link = new EntityLink();
		$link->fromEntityUniqueId = $this->getId();
		$link->type = EntityLink::TYPE_RIDER;
		$link->toEntityUniqueId = $entity->getId();
		$link->immediate = true;
		if($entity instanceof Player) {
			$pk = new SetEntityLinkPacket();
			$pk->link = $link;
			$entity->getNetworkSession()->sendDataPacket($pk);
			$link_2 = new EntityLink();
			$link_2->fromEntityUniqueId = $entity->getId();
			$link_2->type = EntityLink::TYPE_RIDER;
			$link_2->toEntityUniqueId = 0;
			$link_2->immediate = true;
			$pk = new SetEntityLinkPacket();
			$pk->link = $link_2;
			$entity->getNetworkSession()->sendDataPacket($pk);
			unset($viewers[$entity->getId()]);
		}
		return true;
	}
}