<?php
namespace Klassnoenazvanie\Helpers;

class TimeToMeet {
    private $meet_day;

    public function __construct($meet_day) {
        $this->meet_day = $meet_day;
    }

    public function show_days_to_meet($days_to_meet) {
        if ($days_to_meet < 0) return '💫 '.$this->num_word(abs($days_to_meet), ['день', 'дня', 'дней']);

        if ($days_to_meet == 0) return '💫 Сегодня';
        if ($days_to_meet > 0) return '💜 Хороших выходных';
    }

    public function compute_days_to_meet() {
        $now = time();
        $your_date = strtotime($_ENV['MEET_DAY']);
        $datediff = $now - $your_date;

        return floor($datediff / (60 * 60 * 24));
    }

    private function num_word($value, $words, $show = true) 
    {
        $num = $value % 100;
        if ($num > 19) { 
            $num = $num % 10; 
        }
        
        $out = ($show) ?  $value . ' ' : '';
        switch ($num) {
            case 1:  $out .= $words[0]; break;
            case 2: 
            case 3: 
            case 4:  $out .= $words[1]; break;
            default: $out .= $words[2]; break;
        }
        
        return $out;
    }
}