<?php
require_once __DIR__ . '/vendor/autoload.php';

if(intval(date('H')) == 00 && intval(date('i')) == 00){
    $dotenv = Dotenv\Dotenv::createMutable(__DIR__);
    $dotenv->load();

    $vk = new VK\Client\VKApiClient();
    $access_token = $_ENV['ACCESS_TOKEN'];

    $time_to_meet = new Klassnoenazvanie\Helpers\TimeToMeet($_ENV['MEET_DAY']);
    $days_to_meet = $time_to_meet->compute_days_to_meet();

    $meet_message = $time_to_meet->show_days_to_meet($days_to_meet);

    $vk->messages()->send($access_token, [
        'user_id' => $_ENV['IGOR_ID'],
        'random_id' => rand(5, 2147483647),
        'message' => $meet_message,
        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
    ]);
    
    $vk->messages()->send($access_token, [
        'user_id' => $_ENV['OKSY_ID'],
        'random_id' => rand(5, 2147483647),
        'message' => $meet_message,
        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
    ]);
}