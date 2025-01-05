<?php

namespace SoyDavs\UltimateCaptcha;

use pocketmine\plugin\PluginBase;
use SoyDavs\UltimateCaptcha\listeners\EventListener;
use SoyDavs\UltimateCaptcha\commands\CaptchaCommand;

class Main extends PluginBase {

    private $captchaManager;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->captchaManager = new CaptchaManager($this);

        // Register listeners
        $this->getServer()->getPluginManager()->registerEvents(
            new EventListener($this, $this->captchaManager),
            $this
        );

        // Register commands
        $this->getServer()->getCommandMap()->register(
            "ultimatecaptcha",
            new CaptchaCommand($this)
        );
    }

    public function getCaptchaManager(): CaptchaManager {
        return $this->captchaManager;
    }
}
