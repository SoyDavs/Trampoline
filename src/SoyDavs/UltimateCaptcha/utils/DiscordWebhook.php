<?php

namespace SoyDavs\UltimateCaptcha\utils;

use pocketmine\Server;

class DiscordWebhook {

    /**
     * Sends a message to the specified Discord webhook URL.
     * SSL verification is explicitly disabled (INSECURE).
     *
     * @param string $webhookUrl The full Discord Webhook URL
     * @param string $message    The message content to send
     */
    public static function send(string $webhookUrl, string $message): void {
        $data = [
            "content" => $message
        ];
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ch = curl_init($webhookUrl);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if(!empty($error)){
            // If there's a cURL error, it will be logged here
            Server::getInstance()->getLogger()->error("Discord Webhook error: " . $error);
        } else {

        }
    }
}
