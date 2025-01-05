<?php

namespace SoyDavs\UltimateCaptcha\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use SoyDavs\UltimateCaptcha\Main;
use SoyDavs\UltimateCaptcha\CaptchaManager;

class EventListener implements Listener {

    private Main $plugin;
    private CaptchaManager $captchaManager;

    public function __construct(Main $plugin, CaptchaManager $captchaManager){
        $this->plugin = $plugin;
        $this->captchaManager = $captchaManager;
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $config = $this->plugin->getConfig();

        // If you want to skip captcha for ops:
        if(Server::getInstance()->isOp($player->getName())){
            return;
        }

        $showOnEveryJoin = (bool) $config->getNested("Captcha.ShowOnEveryJoin", false);

        // If "ShowOnEveryJoin" is TRUE, always show the captcha,
        // Otherwise, only if not verified
        if($showOnEveryJoin || !$this->captchaManager->isVerified($player)){
            $this->captchaManager->showCaptcha($player);
        }
    }
}
