<?php
namespace Klassnoenazvanie\Handlers;

use Klassnoenazvanie\Helpers\TimeToMeet;

class StatsHandler {
    private $vk;
    private $access_token;
    private $entityManager;

    public function __construct($vk, $access_token, $entityManager) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->entityManager = $entityManager;
    }

    public function getStats($group_id, $secret, $object, $user) {
        $startDate = strtotime("2022-03-11");
        $now = time();
        $dateDiff = $now - $startDate;

        $daysFromStart = round((($dateDiff / 60) / 60) / 24);
        $daysFromStartPrint = TimeToMeet::num_word($daysFromStart, ['день', 'дня', 'дней']);

        $allRemindersCount = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->createQueryBuilder('r')->select('count(r.id)')->getQuery()->getSingleScalarResult();
        $activeRemindersCount = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->createQueryBuilder('r')->where('r.done = 0')->select('count(r.id)')->getQuery()->getSingleScalarResult();

        $message = "— Дата старта: 11 марта 2022 (активен ".$daysFromStartPrint.")";
        $message = $message."\n— Создано напоминаний: ".$allRemindersCount." (активно ".$activeRemindersCount.")";

        $message_id = $this->vk->messages()->send($this->access_token, [
            'user_id' => $user->getVkId(),
            'random_id' => rand(5, 2147483647),
            'message' => $message,
            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
        ]);
    }
}   