<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/ShoppingList/src/Controllers/ShoppingListController.php';

use Dotenv\Dotenv;
use ShoppingList\Controllers\ShoppingListController;
use Telegram\Bot\Api;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$token = $_ENV['TOKEN'];
$telegram = new Api($token);

$offset = 0;
while (true) {
    $updates = $telegram->getUpdates(['offset' => $offset]);

    foreach ($updates as $update) {
        $message = $update->getMessage();
        $chatId = $update->getChat()->getId();
        $text = $message->getText();

        if ($text == '/start') {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Здравствуй, ' . $message->getChat()->getUsername()
                    . '. Отправь мне список покупок)'
            ]);
        } elseif ($text == '/info') {
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Бота создал Никита Россиин' . PHP_EOL
                    . 'email: n.rossiin98@yandex.ru'
            ]);
        } else {
            $controller = new ShoppingListController($telegram, $update);
            $controller->makeAction($text);
        }

        $offset = $update->getUpdateId() + 1;
    }

    sleep(1); // Опрашивать сервер каждую секунду
}
