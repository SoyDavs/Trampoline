<?php

namespace SoyDavs\UltimateCaptcha\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use SoyDavs\UltimateCaptcha\Main;

class CaptchaForm implements Form {

    private Main $plugin;
    private string $captchaString;

    public function __construct(Main $plugin, string $captchaString){
        $this->plugin = $plugin;
        $this->captchaString = $captchaString;
    }

    public function jsonSerialize(): array {
        $config = $this->plugin->getConfig();

        $title = $config->getNested("Messages.Title", "Captcha Title");
        $description = $config->getNested("Messages.Description", "Please complete the captcha");

        return [
            "type" => "custom_form",
            "title" => $title,
            "content" => [
                [
                    "type" => "label",
                    "text" => $description . "\n\n§eCaptcha: §f" . $this->captchaString
                ],
                [
                    "type" => "input",
                    "text" => "§7Type the above text:"
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void {
        // If the player closes the form or doesn't provide any data
        if($data === null){
            return;
        }

        // data[0] is the label, data[1] is the user's input
        $answer = trim($data[1] ?? "");
        $config = $this->plugin->getConfig();

        $wrongAnswerMsg = $config->getNested("Messages.WrongAnswer", "Incorrect captcha. Try again!");
        $correctAnswerMsg = $config->getNested("Messages.CorrectAnswer", "You have passed!");
        $useCaseSensitive = (bool) $config->getNested("Captcha.UseCaseSensitive", true);

        // Handle case sensitivity
        if(!$useCaseSensitive){
            $answer = strtolower($answer);
            $correct = strtolower($this->captchaString);
        } else {
            $correct = $this->captchaString;
        }

        if($answer === $correct){
            // Correct captcha
            $player->sendMessage($correctAnswerMsg);
            $this->plugin->getCaptchaManager()->setVerified($player);
            $this->plugin->getCaptchaManager()->resetFailAttempts($player);
        } else {
            // Incorrect captcha
            $player->sendMessage($wrongAnswerMsg);
            $this->plugin->getCaptchaManager()->addFailAttempt($player);

            // Reopen the form immediately (no delay)
            $this->plugin->getCaptchaManager()->showCaptcha($player);
        }
    }
}
