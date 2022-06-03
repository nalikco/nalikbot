<?php

use Klassnoenazvanie\Helpers\Keyboards;

require_once "vendor/autoload.php";

$vk = new VK\Client\VKApiClient();
$access_token = "0515836dd13cdde82e2bb97993ccbbc73cf47d99ee63f1161e3bf0ce5ce2e9f87fba49772d0a031579502";

$message = 'Обновление 0.14: новый вид отображения меню и клавиатур.';

$vk->messages()->send($access_token, [
    'user_id' => 170008206,
    'random_id' => rand(5, 2147483647),
    'message' => $message,
    'keyboard' => Keyboards::getMain()
]);

$vk->messages()->send($access_token, [
    'user_id' => 152123144,
    'random_id' => rand(5, 2147483647),
    'message' => $message,
    'keyboard' => Keyboards::getMain()
]);