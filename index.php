<?php
require_once __DIR__ . '/vendor/autoload.php';

$vk = new VK\Client\VKApiClient();
$access_token = '0515836dd13cdde82e2bb97993ccbbc73cf47d99ee63f1161e3bf0ce5ce2e9f87fba49772d0a031579502';
$group_id = 211151815;
$wait = 25;

$handler = new Klassnoenazvanie\Handlers\SmokeHandler();
$executor = new VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor($vk, $access_token, $group_id, $handler, $wait);

while(true) {
    $executor->listen();
}