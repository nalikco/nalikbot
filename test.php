<?php
require_once __DIR__ . '/bootstrap.php';

use Klassnoenazvanie\Helpers\TimeToMeet;

$message = "Список активных напоминаний:\n\n";

$activeReminders = $entityManager->getRepository('Klassnoenazvanie\Reminder')->findBy(['done' => 0]);

foreach($activeReminders as $reminder) {
    $now = time();
    $datediff = $now - $reminder->getDate()->getTimestamp();

    if ($datediff < 0){
        $today = new DateTime("today");

        $match_date = $reminder->getDate();

        $diff = $today->diff( $match_date );
        $diffDays = (integer)$diff->format( "%R%a" );

        switch( $diffDays ) {
            case 0:
                $date = "Сегодня в ".$reminder->getDate()->format("H:i");
                break;
            case +1:
                $date = "Завтра в ".$reminder->getDate()->format("H:i");
                break;
            default:
                $formatter = new \IntlDateFormatter('ru_RU', IntlDateFormatter::FULL, IntlDateFormatter::FULL);
                $formatter->setPattern('dd MMMM YYYY в HH:mm');
                $date = $formatter->format($reminder->getDate());
                break;
        }

        $seconds_to_date = abs($datediff);
        $timeToDate = "";

        $minutes = round($seconds_to_date / 60);
        $hours = round($minutes / 60);

        if ($hours >= 24) {
            $days = round($hours / 24);

            $timeToDate = TimeToMeet::num_word($days, ['день', 'дня', 'дней']).' и '.TimeToMeet::num_word($hours - $days * 24, ['час', 'часа', 'часов']);
        } else $timeToDate = TimeToMeet::num_word($hours, ['час', 'часа', 'часов']);


        $message = $message."— ".$date.": ".$reminder->getText()." (через ".$timeToDate.")\n";
    }
}

echo $message;