<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\shapes\Cuboid;
use muqsit\worldstyler\utils\Utils;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat as TF;

class SetCommandExecutor extends BaseCommandExecutor {

    protected function initExecutor() : void
    {
        $this->addOption("async", self::OPT_OPTIONAL, true);
    }

    public function onCommandExecute(CommandSender $sender, Command $command, string $label, array $args, array $opts) : bool
    {
        $selection = $this->plugin->getPlayerSelection($sender);
        $count = $selection->getPositionCount();

        if ($count < 2) {
            $sender->sendMessage(TF::RED . 'You have not selected enough vertices.');
            return false;
        }

        if (!isset($args[1])) {
            $sender->sendMessage(TF::RED . '//set <block> <block2>');
            return false;
        }

        $block1 = Utils::getBlockFromString($args[0]);
        if ($block1 === null) {
            $sender->sendMessage(TF::RED . 'Invalid block given.');
            return false;
        }

	    $block2 = Utils::getBlockFromString($args[1]);
	    if ($block2 === null) {
		    $sender->sendMessage(TF::RED . 'Invalid block given.');
		    return false;
	    }


        $cuboid = Cuboid::fromSelection($selection);
        $force_async = $opts["async"] ?? null;
        if ($force_async !== null ? ($force_async = $this->getBool($force_async)) : $this->plugin->getConfig()->get("use-async-tasks", false)) {
            $cuboid = $cuboid->async();
        }

        if ($force_async !== null) {
            $sender->sendMessage(TF::GRAY . 'Asynchronous /' . $label . ' ' . ($force_async ? 'enabled' : 'disabled'));
        }

        $cuboid->set(
            $sender->getWorld(),
            [$block1->getFullId(), $block2->getFullId()],
            function (float $time, int $changed) use ($sender) : void {
                $sender->sendMessage(TF::GREEN . 'Set ' . number_format($changed) . ' blocks in ' . number_format($time, 10) . 's');
            }
        );
        return true;
    }
}
