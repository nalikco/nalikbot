<?php
require_once __DIR__ . '/bootstrap.php';

exec("vendor/bin/doctrine orm:schema-tool:update --force --dump-sql");

$vk = new VK\Client\VKApiClient();
$access_token = getenv('ACCESS_TOKEN');
$group_id = getenv('GROUP_ID');
$wait = 25;

$handler = new Klassnoenazvanie\Handlers\Handler($vk, $access_token, $entityManager);
$executor = new VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor($vk, $access_token, $group_id, $handler, $wait);

while(true) {
    $executor->listen();
}