<?php
namespace Klassnoenazvanie\Handlers;

use Exception;
use Klassnoenazvanie\Helpers\Dates;
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

    /**
     * @throws Exception
     */
    public function getStats($user, int $conversation_message_id): void
    {
        $startDate = strtotime("2022-03-11");
        $now = time();
        $dateDiff = $now - $startDate;

        $daysFromStart = round((($dateDiff / 60) / 60) / 24);
        $daysFromStartPrint = TimeToMeet::num_word($daysFromStart, ['день', 'дня', 'дней']);

        $allRemindersCount = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->createQueryBuilder('r')->select('count(r.id)')->getQuery()->getSingleScalarResult();
        $activeRemindersCount = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->createQueryBuilder('r')->where('r.done = 0')->select('count(r.id)')->getQuery()->getSingleScalarResult();

        $message = "— Дата старта: 11 марта 2022 (активен ".$daysFromStartPrint.")";
        $message = $message."\n— Текущая версия: 0.14_1 (5 июня 2022)";
        $message = $message."\n— Создано напоминаний: ".$allRemindersCount." (активно ".$activeRemindersCount.")";

        $timeToMeet = new TimeToMeet();
        $daysToMeet = $timeToMeet->compute_days_to_meet();

        if ($daysToMeet < 0 || $daysToMeet == 0) {

            $message = $message."\n— До следующей встречи: ".$timeToMeet->show_days_to_meet($daysToMeet);
            if ($daysToMeet < 0) {
                $meetDate = new \DateTime(getenv('MEET_DAY'));
                $message = $message." (".Dates::formatDate($meetDate, true).")";
            }
        }

        $this->vk->messages()->edit($this->access_token, [
            'peer_id' => $user->getVkId(),
            'message' => $message,
            'conversation_message_id' => $conversation_message_id
        ]);
    }
}   