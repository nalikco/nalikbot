<?php
require_once __DIR__ . '/bootstrap.php';

$vk = new VK\Client\VKApiClient();
$access_token = $_ENV['ACCESS_TOKEN'];

$vk->messages()->send($access_token, [
    'user_id' => 170008206,
    'random_id' => rand(5, 2147483647),
    'message' => "Обновление 0.12: теперь напоминания показываются двоим. Также, добавилась возможность удалять напоминания друг друга.",
    'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
]);