<?php

require_once __DIR__.'/vendor/autoload.php';

use Telegram\Bot\Api;

$token = '6737234994:AAECdOJQGV6c-5RSseACLCNz9qjIc0zTNy8';
$telegram = new Api($token);

$offset = 0;
while (true) {
    $updates = $telegram->getUpdates(['offset' => $offset]);

    foreach ($updates as $update) {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Вы сказали: ' . $text
        ]);

        $offset = $update->getUpdateId() + 1;
    }
    sleep(1); // Опрашивать сервер каждую секунду
}
