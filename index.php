<?php
require_once __DIR__ . '/bootstrap.php';

$vk = new VK\Client\VKApiClient();
$access_token = $_ENV['ACCESS_TOKEN'];
$group_id = $_ENV['GROUP_ID'];
$wait = 25;

$handler = new Klassnoenazvanie\Handlers\Handler($vk, $access_token, $entityManager);
$executor = new VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor($vk, $access_token, $group_id, $handler, $wait);

while(true) {
    $executor->listen();
}