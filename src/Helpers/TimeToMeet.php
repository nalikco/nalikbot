<?php
namespace Klassnoenazvanie\Helpers;

class TimeToMeet {
    public function show_days_to_meet($days_to_meet): string
    {
        if ($days_to_meet < -1) return '💫 '.TimeToMeet::num_word(abs($days_to_meet), ['день', 'дня', 'дней']);

        if ($days_to_meet == 0) return '💫 Сегодня';
        if ($days_to_meet == -1) return '💫 Завтра';
        if ($days_to_meet > 0) return '';
    }

    public function compute_days_to_meet(): float
    {
        $now = time();
        $your_date = strtotime(getenv('MEET_DAY'));
        $dateDiff = $now - $your_date;

        return floor($dateDiff / (60 * 60 * 24));
    }

    public static function num_word($value, $words, $show = true): string
    {
        $num = $value % 100;
        if ($num > 19) {
            $num = $num % 10;
        }

        $out = ($show) ?  $value . ' ' : '';
        $out .= match ($num) {
            1 => $words[0],
            2, 3, 4 => $words[1],
            default => $words[2],
        };

        return $out;
    }
}
