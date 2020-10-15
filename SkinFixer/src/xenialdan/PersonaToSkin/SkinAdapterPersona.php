<?php

declare(strict_types=1);

namespace xenialdan\PersonaToSkin;

use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\SkinData;

class SkinAdapterPersona extends LegacySkinAdapter
{
    public function fromSkinData(SkinData $data): Skin
    {
        if ($data->isPersona()) {
            return Loader::getRandomSkin();
        }
        return parent::fromSkinData($data); // TODO: Change the autogenerated stub
    }

}