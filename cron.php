<?php
require_once __DIR__ . '/bootstrap.php';

$vk = new VK\Client\VKApiClient();
$access_token = $_ENV['ACCESS_TOKEN'];

$activeReminders = $entityManager->getRepository('Klassnoenazvanie\Reminder')->findBy(['done' => 0]);

foreach($activeReminders as $reminder) {
    $now = time();
    $datediff = $now - $reminder->getDate()->getTimestamp();

    if ($datediff >= 0){
        $vk->messages()->send($access_token, [
            'user_id' => $_ENV['IGOR_ID'],
            'random_id' => rand(5, 2147483647),
            'message' => '✳️ Напоминание: '.$reminder->getText(),
        ]);

        $vk->messages()->send($access_token, [
            'user_id' => $_ENV['OKSY_ID'],
            'random_id' => rand(5, 2147483647),
            'message' => '✳️ Напоминание: '.$reminder->getText(),
        ]);

        $reminder->setDone(1);

        $entityManager->persist($reminder);
        $entityManager->flush();
    }
}

if(intval(date('H')) == 00 && intval(date('i')) == 00){
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