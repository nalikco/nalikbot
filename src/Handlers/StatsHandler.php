<?php
namespace Klassnoenazvanie\Handlers;

use Klassnoenazvanie\Helpers\TimeToMeet;
use Klassnoenazvanie\Helpers\Keyboards;

class StatsHandler {
    private $vk;
    private $access_token;
    private $entityManager;

    public function __construct($vk, $access_token, $entityManager) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->entityManager = $entityManager;
    }

    public function getStats($user): void
    {
        $startDate = strtotime("2022-03-11");
        $now = time();
        $dateDiff = $now - $startDate;

        $daysFromStart = round((($dateDiff / 60) / 60) / 24);
        $daysFromStartPrint = TimeToMeet::num_word($daysFromStart, ['день', 'дня', 'дней']);

        $allRemindersCount = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->createQueryBuilder('r')->select('count(r.id)')->getQuery()->getSingleScalarResult();
        $activeRemindersCount = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->createQueryBuilder('r')->where('r.done = 0')->select('count(r.id)')->getQuery()->getSingleScalarResult();

        $message = "— Дата старта: 11 марта 2022 (активен ".$daysFromStartPrint.")";
        $message = $message."\n— Создано напоминаний: ".$allRemindersCount." (активно ".$activeRemindersCount.")";

        $this->vk->messages()->send($this->access_token, [
            'user_id' => $user->getVkId(),
            'random_id' => rand(5, 2147483647),
            'message' => $message,
            'keyboard' => Keyboards::getMain()
        ]);
    }
}   