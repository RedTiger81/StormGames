<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\island\island;

use Eren5960\SkyBlock\CobblestoneReward;
use Eren5960\SkyBlock\island\handler\LevelHandler;
use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\island\IslandOptions;
use Eren5960\SkyBlock\pass\Pass;
use Eren5960\SkyBlock\pass\PassManager;
use Eren5960\SkyBlock\SkyBlock;
use Eren5960\SkyBlock\SkyPlayer;
use Eren5960\SkyBlock\utils\Compression;
use PhpParser\Node\Expr\ClosureUse;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Liquid;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\HandlerListManager;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\player\GameMode;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\sound\FizzSound;
use pocketmine\world\World;
use pocketmine\player\Player;
use pocketmine\Server;
use ZipArchive;

abstract class IslandBase implements Listener{
    /** @var IslandOptions */
    public $options;
    /** @var string */
    public $owner;
    /** @var string */
    public $island;
    /** @var bool */
    public $closed;
	/** @var bool */
	public $new;

	abstract public function getCobblestoneId(): int;
	abstract public function doChestContents(ChestInventory $inventory): void;

    public function __construct(string $name, string $island, bool $new){
        $this->owner = $name;
        $this->island = $island;
        $this->new = $new;
        $this->options = new IslandOptions($this);
        SkyBlock::registerListener($this);
    }

    public function extractLevel(): void{
        $archive = new ZipArchive();
        $archive->open(IslandManager::getIslandZip($this->owner, $this->getName()));
        $archive->extractTo(Server::getInstance()->getDataPath() . 'worlds' . DS);
        $archive->close();
        $this->loadWorld();
    }

    public function loadWorld(): void{
	    Server::getInstance()->getWorldManager()->loadWorld($this->owner . '-' . $this->getName());
		$this->getWorld()->setAutoSave(true);
    }

    /** @return string */
     public function getName(): string{
         return $this->island;
     }

	public function getPlayersCount(): int{
		return $this->getWorld() !== null ? count($this->getWorld()->getPlayers()) : 0;
	}

	public function inIsland(Player $player): bool{
		return $this->getWorld() === null ? false : $player->getWorld()->getId() === $this->getWorld()->getId();
	}

	public function isOwner(Player $player): bool{
		return strtolower($this->owner) === strtolower($player->getName());
	}

	public function getOwner(): ?SkyPlayer{
		return Server::getInstance()->getPlayerExact($this->owner);
	}

	public function getWorld(): ?World{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->owner . '-' . $this->island);
	}

	public $closeTime = 5;

	public function onTick(int $currentTick): void{
		if($currentTick % 20 === 0){
			if($this->getPlayersCount() === 0 && $this->getOwner() === null && $this->closeTime-- === 0){
				IslandManager::closeIsland($this);
			}
		}
	}

    public function teleport(Player ...$players){
        if($this->getWorld() === null) $this->loadWorld();
	    foreach ($players as $player) {
		    if($this->options->locked && $player->getName() !== $this->owner){
			    $player->sendMessage("§7» §cBu ada kilitli olduğu için giremezsin!");
			    continue;
		    }
		    $player->sendPopup("Ada hazırlanıyor.");
		    $player->setGamemode(GameMode::SURVIVAL());
		    $player->teleport(Position::fromObject($this->getWorld()->getSpawnLocation()->add(0, 2), $this->getWorld()));
		    $player->sendTitle("§f" . $this->options->name, "§7Sv. §b" . $this->options->level . "  §7XP: §a" . number_format($this->options->xp, 2) . '§7/§c' . number_format($this->options->need_xp, 2));
		    $player->setImmobile();
	    }
        SkyBlock::getAPI()->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) use($players): void{
	        foreach($players as $player) $player->setImmobile(false);
        }), 60);
    }

    public function close(): void{
        if($this->getWorld() !== null){
            $this->getWorld()->save();
            Server::getInstance()->getWorldManager()->unloadWorld($this->getWorld());
        }
        HandlerListManager::global()->unregisterAll($this);
        Compression::saveIsland($this);
	    $this->options->save();
        Compression::remove(Server::getInstance()->getDataPath() . 'worlds' . DS . $this->owner . '-' . $this->getName());
        $this->closed = true;
    }

    public function onEntityTeleport(EntityTeleportEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player && $this->inIsland($player) && $event->getTo()->getWorld()->getFolderName() !== $this->getWorld()->getFolderName()){
            $this->forceUnload();
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        if($this->inIsland($player) && $this->getPlayersCount() === 0){
	        $this->forceUnload();
        }
    }

    private function forceUnload(): void{
	    if($this->getPlayersCount() === 0 && $this->getWorld() instanceof World){
	    	$this->getWorld()->save(true);
	    	Server::getInstance()->getWorldManager()->unloadWorld($this->getWorld());
	    }
    }

    public function onBlockPlace(BlockPlaceEvent $event){
	    /** @var SkyPlayer $player */
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if (!$this->inIsland($player)) return;

	    if(!$this->handleChange($player, Pass::PLACE_BLOCK)){
		    $event->setCancelled();
		    return;
	    }
        if($block->getId() !== $this->getCobblestoneId()) LevelHandler::up($block, $this);
    }

	public function onCobblestone(BlockFormEvent $event){
     	$block = $event->getBlock();
     	$pos = $block->getPos();
     	$replace = null;

		if($this->island !== DEFAULT_ISLAND){
			if($block->getId() === BlockLegacyIds::FLOWING_LAVA && $event->getNewState()->getId() === BlockLegacyIds::COBBLESTONE && $pos->getWorld() !== null){
				$event->setCancelled();
				$pos->getWorld()->setBlock($pos, BlockFactory::get($this->getCobblestoneId()));
				$pos->getWorld()->addSound($pos->add(0.5, 0.5, 0.5), new FizzSound(2.6 + (lcg_value() - lcg_value()) * 0.8));
			}
		}
	}

    public function onBlockBreak(BlockBreakEvent $event){
     	/** @var SkyPlayer $player */
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(!$this->inIsland($player)) return;

        if(!$this->handleChange($player, Pass::BREAK_BLOCK)){
        	$event->setCancelled(true);
        	return;
        }
        if($block->getId() === $this->getCobblestoneId()){
            CobblestoneReward::run($event);
            return;
        }
        LevelHandler::down($block, $this);
    }

    public function handleChange(SkyPlayer $player, string $pass): bool{
     	$opt = $this->options;
     	if($this->isOwner($player)) return true;

	    if(!$opt->isMember($player)){
		    $player->sendPopup("§8» §cOrtak değilsin §8«");
		    return false;
	    }
	    if(!$opt->getMember($player)->hasPass(PassManager::getPass($pass))){
		    $player->sendPopup("§8» §cİznin yok §8«");
		    return false;
	    }
     	return true;
    }

    public function onIncentoryOpen(InventoryOpenEvent $event){
		$player = $event->getPlayer();
		$inventory = $event->getInventory();
		if($this->new && $inventory instanceof ChestInventory){
			$this->doChestContents($inventory);
			$this->new = false;
		}
		$event->setCancelled(!$this->handleChange($player, Pass::OPEN_CONTAINER));
    }

    public function __destruct(){
		$this->options = null;
		$this->owner = null;;
		$this->island = null;
	}
}