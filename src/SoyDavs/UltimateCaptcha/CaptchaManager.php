<?php

namespace SoyDavs\UltimateCaptcha;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use SoyDavs\UltimateCaptcha\forms\CaptchaForm;
use SoyDavs\UltimateCaptcha\utils\DiscordWebhook;

class CaptchaManager {

    /** @var Main */
    private $plugin;
    /** @var Config Main config.yml */
    private $config;
    /** @var Config Our verifiedPlayers.yml */
    private $verifiedConfig;

    /** 
     * @var array<string, int> 
     * Stores how many times a player has failed the captcha.
     */
    private $failAttempts = [];

    /** 
     * @var array<string, bool> 
     * Stores which players have successfully completed the captcha.
     */
    private $verifiedPlayers = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->getConfig();

        // Make sure the plugin data folder exists (plugins/UltimateCaptcha/)
        @mkdir($plugin->getDataFolder());

        // Create or load verifiedPlayers.yml inside the plugin's data folder
        $this->verifiedConfig = new Config($plugin->getDataFolder() . "verifiedPlayers.yml", Config::YAML);

        // Load existing verified players from verifiedPlayers.yml
        foreach($this->verifiedConfig->getAll() as $playerName => $value){
            // We expect $value to be boolean
            $this->verifiedPlayers[$playerName] = (bool) $value;
        }
    }

    /**
     * Sends the captcha form to the player.
     */
    public function showCaptcha(Player $player): void {
        $captchaString = $this->generateCaptchaString();
        $form = new CaptchaForm($this->plugin, $captchaString);
        $player->sendForm($form);
    }

    /**
     * Generates a random captcha string based on config.yml settings.
     */
    public function generateCaptchaString(): string {
        $length = (int) $this->config->getNested("Captcha.RandomTextLength", 6);
        $useCaseSensitive = (bool) $this->config->getNested("Captcha.UseCaseSensitive", true);

        $characters = "abcdefghijklmnopqrstuvwxyz0123456789";
        if($useCaseSensitive){
            $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }

        $captcha = "";
        for($i = 0; $i < $length; $i++){
            $captcha .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $captcha;
    }

    /**
     * Checks if the player is already verified.
     */
    public function isVerified(Player $player): bool {
        return isset($this->verifiedPlayers[$player->getName()]);
    }

    /**
     * Marks the player as verified and saves it to verifiedPlayers.yml
     */
    public function setVerified(Player $player): void {
        $this->verifiedPlayers[$player->getName()] = true;

        // Immediately save changes to the verifiedPlayers.yml
        $this->saveData();
    }

    /**
     * Increments the fail attempt counter for a player.
     * If max attempts is exceeded, sends a Discord notification.
     */
    public function addFailAttempt(Player $player): void {
        $name = $player->getName();
        $this->failAttempts[$name] = ($this->failAttempts[$name] ?? 0) + 1;

        $maxAttempts = (int) $this->config->getNested("Captcha.MaxAttempts", 3);
        if($this->failAttempts[$name] >= $maxAttempts){
            // The player is suspicious
            $this->notifySuspicious($player);
        }
    }

    /**
     * Notifies the Discord channel if NotifyOnSuspicious is true.
     */
    public function notifySuspicious(Player $player): void {
        $notify = (bool) $this->config->getNested("Captcha.NotifyOnSuspicious", true);
        if(!$notify) return;

        $webhookURL = $this->config->getNested("Discord.WebhookURL", "");
        if($webhookURL !== ""){
            $messageTemplate = $this->config->getNested(
                "Discord.SuspiciousMessage",
                "Player {player} might be a bot! Too many captcha failures."
            );
            $message = str_replace("{player}", $player->getName(), $messageTemplate);

            DiscordWebhook::send($webhookURL, $message);
        }
    }

    /**
     * Resets the fail attempts count when the player completes the captcha.
     */
    public function resetFailAttempts(Player $player): void {
        unset($this->failAttempts[$player->getName()]);
    }

    /**
     * Saves all verified players to verifiedPlayers.yml
     */
    public function saveData(): void {
        // Write each verified player to the verifiedConfig
        foreach($this->verifiedPlayers as $playerName => $verified) {
            $this->verifiedConfig->set($playerName, $verified);
        }
        $this->verifiedConfig->save();
    }
}
