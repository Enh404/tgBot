<?php

namespace ShoppingList\Controllers;

use ShoppingList\Repositories\ShoppingListSQLRepository;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

class ShoppingListController
{
    private const ADD_PRODUCT = 'Добавить продукт';
    private Api $telegram;
    private Update $update;
    private ShoppingListSQLRepository $repository;

    public function __construct(Api $telegram, Update $update)
    {
        $this->telegram = $telegram;
        $this->update = $update;
        $this->repository = new ShoppingListSQLRepository();
    }

    private function getTelegram(): Api
    {
        return $this->telegram;
    }

    private function getUpdate(): Update
    {
        return $this->update;
    }

    private function getRepository(): ShoppingListSQLRepository
    {
        return $this->repository;
    }

    private function getKeyboard(): Keyboard
    {
        $keyboard = new Keyboard();
        $keyboard->row([self::ADD_PRODUCT]);
        return $keyboard;
    }

    public function makeAction(string $text): void
    {
        if ($text == self::ADD_PRODUCT) {
            $this->addItem();
        } else {
            $text = $this->prepareMessage($text);
            if ($this->getRepository()
                ->checkProductListExist($this->getUpdate()->getChat()->getUsername())
            ) {
                $this->productListUpdate($text);
            } else {
                $this->newProductList($text);
            }
        }
    }

    private function prepareMessage(string $text): array
    {
        $spisok = [];
        foreach (explode(',', $text) as $item) {
            $spisok[] = trim($item);
        }
        return $spisok;
    }

    private function addItem(): void
    {
        $this->getTelegram()->sendMessage([
            'chat_id' => $this->getUpdate()->getChat()->getId(),
            'text' => 'Добавьте продукт(ы)'
        ]);
    }

    private function productListUpdate(array $text): void
    {
        $username = $this->getUpdate()->getChat()->getUsername();
        $productList = $this->getRepository()->getProductsByUsername($username);

        $keyboard = $this->getKeyboard();

        if (in_array(implode($text), $productList)) {
            $newProductList = array_diff($productList, $text);
            if (empty($newProductList)) {
                $this->getRepository()->deleteRow($username);

                $this->getTelegram()->sendMessage([
                    'chat_id' => $this->getUpdate()->getChat()->getId(),
                    'text' => 'Вы купили ' . implode($text) . '!' . PHP_EOL
                        . 'Все продукты куплены!' . PHP_EOL . 'Можете составить новый список.',
                    'reply_markup' => json_encode(['remove_keyboard' => true])
                ]);
            } else {
                $this->getRepository()->updateProductList($username, implode(',', $newProductList));
                foreach ($newProductList as $item) {
                    $keyboard->row([$item]);
                }

                $this->getTelegram()->sendMessage([
                    'chat_id' => $this->getUpdate()->getChat()->getId(),
                    'text' => 'Вы купили ' . implode($text) . '!',
                    'reply_markup' => $keyboard,
                ]);
            }
        } else {
            $newProductList = array_merge(array_diff($productList, $text), $text);
            $this->getRepository()->updateProductList($username, implode(',', $newProductList));
            foreach ($newProductList as $item) {
                $keyboard->row([$item]);
            }

            $this->getTelegram()->sendMessage([
                'chat_id' => $this->getUpdate()->getChat()->getId(),
                'text' => 'Список покупок обновлен:',
                'reply_markup' => $keyboard,
            ]);
        }
    }

    private function newProductList(array $text): void
    {
        $keyboard = $this->getKeyboard();

        foreach ($text as $item) {
            $keyboard->row([$item]);
        }

        $username = $this->getUpdate()->getChat()->getUsername();
        $this->getRepository()->createProductList($username, implode(',', $text));

        $this->getTelegram()->sendMessage([
            'chat_id' => $this->getUpdate()->getChat()->getId(),
            'text' => 'Выберите опцию:',
            'reply_markup' => $keyboard,
        ]);
    }
}
