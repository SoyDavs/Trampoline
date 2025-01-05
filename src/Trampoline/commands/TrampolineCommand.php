<?php

declare(strict_types=1);

namespace Trampoline\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Trampoline\Main;
use Trampoline\listeners\EventListener;

class TrampolineCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct(
            "trampoline",
            "Main command for Trampoline plugin",
            "/trampoline <set|remove|edit|list|cancel>",
            ["tramp"]
        );
        $this->plugin = $plugin;

        // Normally, you can still set a permission here if you want:
        $this->setPermission("trampoline.cmd");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        // Instead of $this->getPermission(), directly check "trampoline.cmd"
        if(!$sender->hasPermission("trampoline.cmd")){
            $sender->sendMessage($this->plugin->getMessage("no-permission"));
            return;
        }

        if(!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("only-in-game"));
            return;
        }

        if(!isset($args[0])) {
            $sender->sendMessage($this->plugin->getMessage("usage"));
            return;
        }

        switch(strtolower($args[0])) {
            case "set":
                $this->handleSet($sender, $args);
                break;

            case "remove":
                $this->handleRemove($sender, $args);
                break;

            case "edit":
                $this->handleEdit($sender, $args);
                break;

            case "list":
                $this->handleList($sender);
                break;

            case "cancel":
                $this->cancelSetMode($sender);
                break;

            default:
                $sender->sendMessage($this->plugin->getMessage("usage"));
                break;
        }
    }

    private function handleSet(Player $player, array $args): void {
        if(isset(EventListener::$setModePlayers[$player->getName()])) {
            $player->sendMessage($this->plugin->getMessage("already-in-set-mode"));
            return;
        }

        if(!isset($args[1]) || !is_numeric($args[1])) {
            $player->sendMessage($this->plugin->getMessage("usage"));
            return;
        }

        $count = (int)$args[1];
        EventListener::$setModePlayers[$player->getName()] = [
            "remaining" => $count,
            "blocks" => []
        ];
        $player->sendMessage($this->plugin->getMessage("set-start", ["count" => $count]));
    }

    private function handleRemove(Player $player, array $args): void {
        if(!isset($args[1]) || !is_numeric($args[1])) {
            $player->sendMessage($this->plugin->getMessage("usage"));
            return;
        }

        $id = (int)$args[1];
        $cfg = $this->plugin->getTrampolinesConfig();
        $tramps = $cfg->get("trampolines", []);

        if(!isset($tramps[$id])) {
            $player->sendMessage($this->plugin->getMessage("remove-fail", ["id" => $id]));
            return;
        }

        unset($tramps[$id]);
        $cfg->set("trampolines", $tramps);
        $cfg->save();
        $player->sendMessage($this->plugin->getMessage("remove-success", ["id" => $id]));
    }

    private function handleEdit(Player $player, array $args): void {
        // Example usage: /trampoline edit <ID> <throwPower> <enableSlimeParticles(true/false)>
        if(!isset($args[1]) || !is_numeric($args[1])) {
            $player->sendMessage($this->plugin->getMessage("usage"));
            return;
        }

        $id = (int)$args[1];
        $cfg = $this->plugin->getTrampolinesConfig();
        $tramps = $cfg->get("trampolines", []);

        if(!isset($tramps[$id])) {
            $player->sendMessage($this->plugin->getMessage("edit-fail"));
            return;
        }

        // Currently we don't store per-trampoline power. This is just a placeholder.
        $player->sendMessage($this->plugin->getMessage("edit-success", ["id" => $id]));
    }

    private function handleList(Player $player): void {
        $cfg = $this->plugin->getTrampolinesConfig();
        $tramps = $cfg->get("trampolines", []);

        if(count($tramps) === 0) {
            $player->sendMessage($this->plugin->getMessage("no-trampolines"));
            return;
        }

        foreach($tramps as $id => $data) {
            $count = count($data["blocks"]);
            $world = $data["blocks"][0]["world"] ?? "unknown";
            $player->sendMessage($this->plugin->getMessage("list-format", [
                "id" => $id,
                "count" => $count,
                "world" => $world
            ]));
        }
    }

    private function cancelSetMode(Player $player): void {
        if(isset(EventListener::$setModePlayers[$player->getName()])) {
            unset(EventListener::$setModePlayers[$player->getName()]);
            $player->sendMessage($this->plugin->getMessage("cancel-success"));
        } else {
            $player->sendMessage($this->plugin->getMessage("cancel-success"));
        }
    }
}
