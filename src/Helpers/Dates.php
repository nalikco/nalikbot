<?php
namespace Klassnoenazvanie\Helpers;

class Dates {
    public static function formatDate(\DateTime $match_date): string
    {
        $today = new \DateTime("today");

        $diff = $today->diff( $match_date );
        $diffDays = (integer)$diff->format("%R%a");

        switch( $diffDays ) {
            case 0:
                $date = "Сегодня в ".$match_date->format("H:i");
                break;
            case +1:
                $date = "Завтра в ".$match_date->format("H:i");
                break;
            default:
                $formatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
                $formatter->setPattern('dd MMMM YYYY в HH:mm');
                $date = $formatter->format($match_date);
                break;
        }

        return $date;
    }

    public static function countdown(int $datediff): string
    {
        $seconds_to_date = abs($datediff);
        $timeToDate = "";

        $minutes = round($seconds_to_date / 60);

        if ($minutes >= 60){
            $hours = round($minutes / 60);

            if ($hours >= 24) {
                $days = round($hours / 24);

                $timeToDate = TimeToMeet::num_word($days, ['день', 'дня', 'дней']);
            } else $timeToDate = TimeToMeet::num_word($hours, ['час', 'часа', 'часов']);
        } else $timeToDate = TimeToMeet::num_word($minutes, ['минута', 'минуты', 'минут']);

        return $timeToDate;
    }
}