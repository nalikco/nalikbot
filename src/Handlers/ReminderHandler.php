<?php
namespace Klassnoenazvanie\Handlers;

use Klassnoenazvanie\Helpers\Dates;
use Klassnoenazvanie\Helpers\Keyboards;

class ReminderHandler {
    private $vk;
    private $access_token;
    private $entityManager;

    public function __construct($vk, $access_token, $entityManager) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->entityManager = $entityManager;
    }

    public function initiate($user, int $conversation_message_id) {
        $this->vk->messages()->edit($this->access_token, [
            'peer_id' => $user->getVkId(),
            'message' => 'Меню напоминаний',
            'conversation_message_id' => $conversation_message_id,
            'keyboard' => Keyboards::getReminderMainMenu()
        ]);
    }

    public function runStep($object, $user) {
        switch($user->getStep()){
            case 0:
                if($object['message']['text'] == 'Отмена') {
                    $user->setApp(0);
                    $user->setStep(0);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => '❎ Создание напоминания отменено',
                        'keyboard' => Keyboards::getMain()
                    ]);
                }

                $wrong_date_message = "⏺ Некорректный формат\nВведите дату, время и текст напоминания в формате:\n\nДата (дд-мм-гггг ЧЧ:ММ)\nТекст";

                $matches = [];
                preg_match('~^(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2})\n(.*)$~', $object['message']['text'], $matches);

                if($matches){
                    $year = intval($matches[3]);
                    $month = intval($matches[2]);
                    $day = intval($matches[1]);
                    $hour = intval($matches[4]);
                    $minute = intval($matches[5]);
                    $text = $matches[6];

                    if (!checkdate($month, $day, $year)) {
                        $this->vk->messages()->send($this->access_token, [
                            'user_id' => $user->getVkId(),
                            'random_id' => rand(5, 2147483647),
                            'message' => $wrong_date_message,
                            'keyboard' => Keyboards::getWithCancel()
                        ]);
                    }

                    if($hour > 24 || $hour < 0 || $minute > 60 || $minute < 0) {
                        $this->vk->messages()->send($this->access_token, [
                            'user_id' => $user->getVkId(),
                            'random_id' => rand(5, 2147483647),
                            'message' => $wrong_date_message,
                            'keyboard' => Keyboards::getWithCancel()
                        ]);
                    }

                    $date_string = sprintf("%'.04d-%'.02d-%'.02d %'.02d:%'.02d", $year, $month, $day, $hour, $minute);
                    $date = strtotime($date_string);
                    $now = time();
                    $datediff = $now - $date;

                    if ($datediff >= 0) {
                        $this->vk->messages()->send($this->access_token, [
                            'user_id' => $user->getVkId(),
                            'random_id' => rand(5, 2147483647),
                            'message' => $wrong_date_message,
                            'keyboard' => Keyboards::getWithCancel()
                        ]);
                    }

                    $reminder = new \Klassnoenazvanie\Reminder();
                    $reminder->setDate(new \DateTime($date_string));
                    $reminder->setText($text);
                    $reminder->setDone(0);

                    $user->setApp(0);
                    $user->setStep(0);
                    $reminder->setUser($user);
                    $user->getReminders()->add($reminder);

                    $this->entityManager->persist($reminder);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => sprintf("✅ Напоминание успешно создано.\n\nДата и время напоминания: %s\nТекст напоминания: %s", Dates::formatDate($reminder->getDate()), $text),
                        'keyboard' => Keyboards::getMain()
                    ]);

                    if ($user->getVkId() == getenv('IGOR_ID')) {
                        $secondUser = getenv('OKSY_ID');
                        $infoMessage = "@id".getenv('IGOR_ID')." (Игорь) создал напоминание";
                    } else {
                        $secondUser = getenv('IGOR_ID');
                        $infoMessage = "@id".getenv('OKSY_ID')." (Оксана) создала напоминание";
                    }

                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $secondUser,
                        'random_id' => rand(5, 2147483647),
                        'message' => sprintf("%s.\n\nДата и время напоминания: %s\nТекст напоминания: %s", $infoMessage, Dates::formatDate($reminder->getDate()), $text),
                    ]);
                } else {
                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => $wrong_date_message,
                        'keyboard' => Keyboards::getWithCancel()
                    ]);
                }
                break;
            case 2:
                if($object['message']['text'] == 'Отмена') {
                    $user->setApp(0);
                    $user->setStep(0);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => '❎ Удаление напоминания отменено',
                        'keyboard' => Keyboards::getMain()
                    ]);

                    return;
                }

                $reminder_id = intval($object['message']['text']);

                if ($reminder_id == 0) return $this->vk->messages()->send($this->access_token, [
                    'user_id' => $user->getVkId(),
                    'random_id' => rand(5, 2147483647),
                    'message' => '❎ Некорректный ID напоминания',
                    'keyboard' => Keyboards::getWithCancel()
                ]);

                $reminder = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->findOneBy(['id' => $reminder_id, 'done' => 0]);

                if (!$reminder) return $this->vk->messages()->send($this->access_token, [
                    'user_id' => $user->getVkId(),
                    'random_id' => rand(5, 2147483647),
                    'message' => '❎ Напоминания с таким ID не существует',
                    'keyboard' => Keyboards::getWithCancel()
                ]);

                $reminder->setDone(1);
                $user->setApp(0);
                $user->setStep(0);

                $this->entityManager->persist($user);
                $this->entityManager->persist($reminder);
                $this->entityManager->flush();

                return $this->vk->messages()->send($this->access_token, [
                    'user_id' => $user->getVkId(),
                    'random_id' => rand(5, 2147483647),
                    'message' => '🗑 Напоминание успешно удалено',
                    'keyboard' => Keyboards::getMain()
                ]);

                break;
        }
    }

    public function listActive($user, int $conversation_message_id): void
    {
        $message = "Список активных напоминаний:\n\n";

        $activeReminders = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->findBy(['done' => 0]);

        foreach($activeReminders as $reminder) {
            $now = time();
            $datediff = $now - $reminder->getDate()->getTimestamp();

            if ($datediff < 0){
                $userInfo = $this->vk->users()->get($this->access_token, ['user_id' => $reminder->getUser()->getVkId()])[0];
                $message = $message."\n— ".Dates::formatDate($reminder->getDate()).":\nТекст: ".$reminder->getText()."\n@id".$userInfo['id']." (".$userInfo['first_name']."), ID ".$reminder->getId()." (через ".Dates::countdown($datediff).")\n";
            }
        }

        $this->vk->messages()->edit($this->access_token, [
            'peer_id' => $user->getVkId(),
            'message' => $message,
            'conversation_message_id' => $conversation_message_id,
        ]);
    }

    public function initiateCreating($user, int $conversation_message_id): void
    {
        $user->setApp(1);
        $user->setStep(0);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->vk->messages()->edit($this->access_token, [
            'peer_id' => $user->getVkId(),
            'message' => "⚠️ Введите дату, время и текст напоминания в формате:\n\nДата (дд-мм-гггг ЧЧ:ММ)\nТекст",
            'conversation_message_id' => $conversation_message_id,
            'keyboard' => Keyboards::getWithCancel()
        ]);
    }

    public function initiateDeleting($user, int $conversation_message_id): void
    {
        $user->setApp(1);
        $user->setStep(2);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->vk->messages()->edit($this->access_token, [
            'peer_id' => $user->getVkId(),
            'message' => "⚠️ Введите ID напоминания",
            'conversation_message_id' => $conversation_message_id,
            'keyboard' => Keyboards::getWithCancel()
        ]);
    }
}
