<?php

declare(strict_types=1);

namespace Trampoline\listeners;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use Trampoline\Main;

// Particle classes
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks; // <-- Needed for 'SLIME' block reference

class EventListener implements Listener {

    /**
     * @var array<string, array{
     *    remaining:int,
     *    blocks: array<int, array{world:string,x:int,y:int,z:int}>
     * }>
     */
    public static array $setModePlayers = [];

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Registers blocks broken in "set mode" as trampoline blocks.
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        if(isset(self::$setModePlayers[$name])) {
            $data = &self::$setModePlayers[$name];
            $block = $event->getBlock();

            // Add the block to the 'blocks' array
            $data["blocks"][] = [
                "world" => $block->getPosition()->getWorld()->getFolderName(),
                "x" => $block->getPosition()->getFloorX(),
                "y" => $block->getPosition()->getFloorY(),
                "z" => $block->getPosition()->getFloorZ()
            ];

            // Decrement remaining
            $data["remaining"]--;

            // Cancel so the block does NOT break
            $event->cancel();

            // If done collecting blocks
            if($data["remaining"] <= 0) {
                // Save to trampolines.yml
                $cfg = $this->plugin->getTrampolinesConfig();
                $all = $cfg->get("trampolines", []);
                // Generate new ID
                $id = (count($all) === 0) ? 1 : (max(array_keys($all)) + 1);

                $all[$id] = [
                    "blocks" => $data["blocks"]
                ];
                $cfg->set("trampolines", $all);
                $cfg->save();

                $player->sendMessage($this->plugin->getMessage("set-done", [
                    "id" => $id,
                    "count" => count($data["blocks"])
                ]));

                unset(self::$setModePlayers[$name]);
            }
        }
    }

    /**
     * Launch the player if they walk onto a trampoline block.
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();

        // Only launch if on the ground (avoid repeated mid-air triggers)
        if(!$player->isOnGround()) {
            return;
        }

        // Check block below player's feet
        $posBelow = $player->getPosition()->floor()->down();
        $blockBelow = $player->getWorld()->getBlock($posBelow);

        if($this->isTrampolineBlock($blockBelow)) {
            $this->launchPlayer($player);
        }
    }

    /**
     * Checks if the given block is part of any trampoline in trampolines.yml
     */
    private function isTrampolineBlock(Block $block): bool {
        $cfg = $this->plugin->getTrampolinesConfig();
        $tramps = $cfg->get("trampolines", []);

        $worldName = $block->getPosition()->getWorld()->getFolderName();
        $x = $block->getPosition()->getFloorX();
        $y = $block->getPosition()->getFloorY();
        $z = $block->getPosition()->getFloorZ();

        foreach($tramps as $id => $data){
            foreach($data["blocks"] as $b){
                if(
                    $b["world"] === $worldName &&
                    $b["x"] === $x &&
                    $b["y"] === $y &&
                    $b["z"] === $z
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Launch player upward with the configured power and show "slime-like" particles.
     */
    private function launchPlayer(Player $player): void {
        $power = (float) $this->plugin->getMainConfig()->get("throwPower", 2.0);
        $enableParticles = (bool) $this->plugin->getMainConfig()->get("enableSlimeParticles", true);

        // Add upward velocity
        $player->setMotion($player->getMotion()->add(0, $power, 0));

        // Show a "slime block break" particle if enabled
        if($enableParticles){
            $world = $player->getWorld();
            $pos = $player->getPosition();
            
            // Force using a slime block break effect
            $world->addParticle(
                $pos,
                new BlockBreakParticle(VanillaBlocks::SLIME()) 
            );
        }
    }
}
