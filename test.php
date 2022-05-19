<?php
$matches = [];
preg_match('~^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})$~', '2022-12-19 19:00', $matches);

if($matches) {
    if (!checkdate($matches[2], $matches[3], $matches[1])) {
        echo 'wrong date';
        exit();
    }

    $date = strtotime('2022-05-19 19:00');
    $now = time();
    $datediff = $now - $date;

    if ($datediff >= 0) {
        echo 'wrong date';
    } else echo 'success';
} else {
    echo 'wrong match';
}