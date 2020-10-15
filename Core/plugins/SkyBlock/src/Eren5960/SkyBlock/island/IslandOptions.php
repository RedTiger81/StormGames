<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\island;

use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\SkyBlock;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use ZipArchive;

class IslandOptions {
    /** @var string */
    public $name = "unknown";
    /** @var int */
    public $level = 0;
    /** @var float */
    public $xp = 0.0;
    /** @var float */
    public $need_xp = 25.0;
    /** @var Member[] */
    public $members = [];
    /** @var string[] */
    public $banneds = [];
    /** @var bool */
    public $locked = false;
    /** @var IslandBase */
    public $island;

    public function __construct(IslandBase $island){
        $data = SkyBlock::getIslandData($island->owner, $island->getName());
        $this->name = $data["name"];
        $this->level = $data["level"];
        $this->xp = $data["xp"];
        $this->need_xp = $data["need_xp"];
        $this->locked = $data["locked"] ?? false;
        $this->banneds = $data["banneds"] ?? [];
        foreach ($data["members"] as $member => $pass){
            $this->members[$member] = new Member($member, $pass);
        }
        $this->island = $island;
    }

    public function save(): void{
        $members = [];
	    foreach ($this->members as $name => $member) {
            $members[$name] = $member->getPass();
        }
        $z = new ZipArchive();
        $z->open(IslandManager::getIslandZip($this->island->owner, $this->island->getName()));
        $z->setArchiveComment(yaml_emit([
            "level" => $this->level,
            "xp" => $this->xp,
            "need_xp" => $this->need_xp,
            "name" => $this->name,
            "members" => $members,
            "locked" => $this->locked,
            "banneds" => $this->banneds
        ]));
    }

    public static function encodeVector3(Vector3 $vector3){
        return $vector3->x . ";" . $vector3->y . ";" . $vector3->z;
    }

    public function isMember(Player $player): bool{
        return isset($this->members[$player->getName()]);
    }

	public function getMember(Player $player): ?Member{
		return $this->members[$player->getName()] ?? null;
	}
}