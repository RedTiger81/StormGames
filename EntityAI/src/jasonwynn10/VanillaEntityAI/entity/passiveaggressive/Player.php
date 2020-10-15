<?php
declare(strict_types=1);

namespace jasonwynn10\VanillaEntityAI\entity\passiveaggressive;

use jasonwynn10\VanillaEntityAI\block\Pumpkin;
use jasonwynn10\VanillaEntityAI\entity\hostile\Enderman;
use jasonwynn10\VanillaEntityAI\entity\Interactable;
use jasonwynn10\VanillaEntityAI\entity\Linkable;
use jasonwynn10\VanillaEntityAI\entity\Lookable;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\NbtDataException;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\InGamePacketHandler;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\NormalTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\serializer\NetworkNbtSerializer;
use pocketmine\Server;
use StormGames\SGCore\blocks\BeaconBlock;
use StormGames\SGCore\tiles\Beacon;

class Player extends InGamePacketHandler{
	/** @var \pocketmine\player\Player */
	protected $player;
	protected $session;

	public function __construct(\pocketmine\player\Player $player, NetworkSession $session){
		parent::__construct($player, $session);
		$this->player = $player;
		$this->session = $session;
	}

	/**
	 * @param ActorPickRequestPacket $packet
	 *
	 * @return bool
	 */
	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool{
		$target = $this->player->getWorld()->getEntity($packet->entityUniqueId);
		if($target === null){
			return false;
		}
		if($this->player->isCreative()){
			$item = ItemFactory::get(ItemIds::MONSTER_EGG, $target::NETWORK_ID, 64);
			if(!empty($target->getNameTag())){
				$item->setCustomName($target->getNameTag());
			}
			$this->player->getInventory()->setItem($packet->hotbarSlot, $item);
		}
		return true;
	}

	/**
	 * @param PlayerInputPacket $packet
	 *
	 * @return bool
	 */
	public function handlePlayerInput(PlayerInputPacket $packet) : bool{
		return false; // TODO
	}

	/**
	 * @param InteractPacket $packet
	 *
	 * @return bool
	 */
	public function handleInteract(InteractPacket $packet) : bool{
		$return = parent::handleInteract($packet);
		if($return){
			switch($packet->action){
				case InteractPacket::ACTION_LEAVE_VEHICLE:
					$target = $this->player->getWorld()->getEntity($packet->target);
					$this->player->setTargetEntity($target);
					if($target instanceof Linkable){
						$target->unlink();
					}
					break;
				case InteractPacket::ACTION_MOUSEOVER:
					$target = $this->player->getWorld()->getEntity($packet->target);
					$this->player->setTargetEntity($target);
					// TODO: check distance
					if($target instanceof Lookable){
						if($target instanceof Enderman and $this->player->getArmorInventory()->getHelmet() instanceof Pumpkin) break;
						$target->onPlayerLook($this->player);
					}elseif($target === null){
						$this->player->getNetworkProperties()->setString(EntityMetadataProperties::INTERACTIVE_TAG, ""); // Don't show button anymore
					}
					$return = true;
					break;
				default:
					Server::getInstance()->getLogger()->debug("Unhandled/unknown interaction type " . $packet->action . "received from " . $this->player->getName());
					$return = false;
			}
		}
		return $return;
	}

	/**
	 * @param InventoryTransactionPacket $packet
	 *
	 * @return bool
	 */
	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
		if($packet->trData instanceof UseItemOnEntityTransactionData){
			$target = $this->player->getWorld()->getEntity($packet->trData->getEntityRuntimeId());
			$this->player->setTargetEntity($target);
			$this->player->getNetworkProperties()->setString(EntityMetadataProperties::INTERACTIVE_TAG, ""); // Don't show button anymore
			if($target instanceof Interactable){
				$target->onPlayerInteract($this->player);
				return true;
			}
		}
		if($packet->trData instanceof UseItemTransactionData){
			$pos = $packet->trData->getBlockPos();
			$block = $this->player->getWorld()->getBlock($pos);
			if($block instanceof BeaconBlock){
				$pk = ContainerOpenPacket::blockInvVec3($this->session->getInvManager()->getCurrentWindowId(), WindowTypes::BEACON, $pos);
				$this->session->sendDataPacket($pk);
			}
		}
		if($packet->trData instanceof NormalTransactionData){
			$this->handleNormalTransaction($packet->trData);
			return true;
		}
		return parent::handleInventoryTransaction($packet);
	}

	private function handleNormalTransaction(NormalTransactionData $data) : bool{
		/** @var InventoryAction[] $actions */
		$actions = [];

		$isCrafting = false;
		$isFinalCraftingPart = false;
		foreach($data->getActions() as $networkInventoryAction){
			if(
				$networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_CONTAINER and
				$networkInventoryAction->windowId === ContainerIds::UI and
				$networkInventoryAction->inventorySlot === 50 and
				!$networkInventoryAction->oldItem->equalsExact($networkInventoryAction->newItem)
			){
				$isCrafting = true;
				if(!$networkInventoryAction->oldItem->isNull() and $networkInventoryAction->newItem->isNull()){
					$isFinalCraftingPart = true;
				}
			}elseif(
				$networkInventoryAction->sourceType === NetworkInventoryAction::SOURCE_TODO and (
					$networkInventoryAction->windowId === NetworkInventoryAction::SOURCE_TYPE_CRAFTING_RESULT or
					$networkInventoryAction->windowId === NetworkInventoryAction::SOURCE_TYPE_CRAFTING_USE_INGREDIENT
				)
			){
				$isCrafting = true;
			}

			try{
				$action = $networkInventoryAction->createInventoryAction($this->player);
				if($action !== null){
					$actions[] = $action;
				}
			}catch(\UnexpectedValueException $e){
				if($networkInventoryAction->inventorySlot === 27){
					$actions[] = new SlotChangeAction($this->player->getCurrentWindow(), 0, $networkInventoryAction->oldItem, $networkInventoryAction->newItem);
				}else{
					$this->session->getLogger()->debug("Unhandled inventory action: " . $e->getMessage());
					return false;
				}
			}
		}

		if($isCrafting){
			//we get the actions for this in several packets, so we need to wait until we have all the pieces before
			//trying to execute it

			if($this->craftingTransaction === null){
				$this->craftingTransaction = new CraftingTransaction($this->player, $actions);
			}else{
				foreach($actions as $action){
					$this->craftingTransaction->addAction($action);
				}
			}

			if($isFinalCraftingPart){
				try{
					$this->session->getInvManager()->onTransactionStart($this->craftingTransaction);
					$this->craftingTransaction->execute();
				}catch(TransactionValidationException $e){
					$this->session->getLogger()->debug("Failed to execute crafting transaction: " . $e->getMessage());
					return false;
				}finally{
					$this->craftingTransaction = null;
				}
			}
		}else{
			//normal transaction fallthru
			if($this->craftingTransaction !== null){
				$this->session->getLogger()->debug("Got unexpected normal inventory action with incomplete crafting transaction, refusing to execute crafting");
				$this->craftingTransaction = null;
				return false;
			}

			if(count($actions) === 0){
				//TODO: 1.13+ often sends transactions with nothing but useless crap in them, no need for the debug noise
				return true;
			}

			$transaction = new InventoryTransaction($this->player, $actions);
			$this->session->getInvManager()->onTransactionStart($transaction);
			try{
				$transaction->execute();
			}catch(TransactionValidationException $e){
				$logger = $this->session->getLogger();
				$logger->debug("Failed to execute inventory transaction: " . $e->getMessage());
				$logger->debug("Actions: " . json_encode($data->getActions()));

				return false;
			}
		}

		return true;
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		$this->player->doCloseInventory();

		if($packet->windowId === 255){
			//Closed a fake window
			return true;
		}

		if($packet->windowId !== $this->session->getInvManager()->getCurrentWindowId()){
			$this->player->getCurrentWindow()->onClose($this->player);
		}else{
			$this->session->getInvManager()->onClientRemoveWindow($packet->windowId);
		}

		return true;
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool{
		$pos = new Vector3($packet->x, $packet->y, $packet->z);
		if($pos->distanceSquared($this->player->getLocation()) > 10000){
			return false;
		}

		$tile = $this->player->getLocation()->getWorld()->getTile($pos);
		try{
			$offset = 0;
			$nbt = (new NetworkNbtSerializer())->read($packet->namedtag, $offset, 512)->mustGetCompoundTag();
		}catch(NbtDataException $e){
			throw BadPacketException::wrap($e);
		}
		if($tile instanceof Beacon){
			$tile->updateCompoundTag($nbt);
			$tile->getInventory()->clearAll();
		}
		return parent::handleBlockActorData($packet);
	}
}