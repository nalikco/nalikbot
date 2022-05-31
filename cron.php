<?php
require_once __DIR__ . '/bootstrap.php';

$vk = new VK\Client\VKApiClient();
$access_token = getenv('ACCESS_TOKEN');

$activeReminders = $entityManager->getRepository('Klassnoenazvanie\Reminder')->findBy(['done' => 0]);

foreach($activeReminders as $reminder) {
    $now = time();
    $datediff = $now - $reminder->getDate()->getTimestamp();

    if ($datediff >= 0){
        $message = '✳️ '.$reminder->getText();

        $vk->messages()->send($access_token, [
            'user_id' => getenv('IGOR_ID'),
            'random_id' => rand(5, 2147483647),
            'message' => $message,
        ]);

        $vk->messages()->send($access_token, [
            'user_id' => getenv('OKSY_ID'),
            'random_id' => rand(5, 2147483647),
            'message' => $message,
        ]);

        $reminder->setDone(1);

        $entityManager->persist($reminder);
        $entityManager->flush();
    }
}

if(intval(date('H')) == 00 && intval(date('i')) == 00){
    $time_to_meet = new Klassnoenazvanie\Helpers\TimeToMeet();
    $days_to_meet = $time_to_meet->compute_days_to_meet();

    $meet_message = $time_to_meet->show_days_to_meet($days_to_meet);

    $vk->messages()->send($access_token, [
        'user_id' => getenv('IGOR_ID'),
        'random_id' => rand(5, 2147483647),
        'message' => $meet_message,
    ]);
    
    $vk->messages()->send($access_token, [
        'user_id' => getenv('OKSY_ID'),
        'random_id' => rand(5, 2147483647),
        'message' => $meet_message,
    ]);
}