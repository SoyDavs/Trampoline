<?php

declare(strict_types=1);

namespace Trampoline;

use pocketmine\plugin\PluginBase;
use Trampoline\commands\TrampolineCommand;
use Trampoline\listeners\EventListener;
use pocketmine\utils\Config;

class Main extends PluginBase {

    /** @var Config */
    private Config $configYml;
    /** @var Config */
    private Config $langYml;
    /** @var Config */
    private Config $trampolinesYml;

    private static self $instance;

    protected function onEnable(): void {
        self::$instance = $this;

        // Save default configs (config.yml, lang.yml, trampolines.yml)
        $this->saveResource("config.yml");
        $this->saveResource("lang.yml");
        $this->saveResource("trampolines.yml");

        $this->configYml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->langYml = new Config($this->getDataFolder() . "lang.yml", Config::YAML);
        $this->trampolinesYml = new Config($this->getDataFolder() . "trampolines.yml", Config::YAML);

        // Register command
        $this->getServer()->getCommandMap()->register("trampoline", new TrampolineCommand($this));

        // Register event listener
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function getMainConfig(): Config {
        return $this->configYml;
    }

    public function getLangConfig(): Config {
        return $this->langYml;
    }

    public function getTrampolinesConfig(): Config {
        return $this->trampolinesYml;
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * Get a message from lang.yml with optional replacements
     */
    public function getMessage(string $key, array $replacements = []): string {
        $msg = $this->langYml->get($key, $key);
        foreach($replacements as $search => $replace){
            $msg = str_replace("%$search%", (string) $replace, $msg);
        }
        return $this->langYml->get("prefix", "") . $msg;
    }
}
