<?php
declare(strict_types=1);
namespace jasonwynn10\VanillaEntityAI\entity;

use pocketmine\entity\Entity;

interface Linkable {
	/**
	 * @return Entity|Linkable|null
	 */
	public function getLink();

	/**
	 * @param Linkable|null $entity
	 *
	 * @return Entity|Linkable
	 */
	public function setLink(?Linkable $entity);

	/**
	 * @return bool
	 */
	public function unlink() : bool;
}