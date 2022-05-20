<?php
namespace Klassnoenazvanie\Handlers;

class ReminderHandler {
    private $vk;
    private $access_token;
    private $entityManager;

    public function __construct($vk, $access_token, $entityManager) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->entityManager = $entityManager;
    }

    public function initiate($group_id, $secret, $object, $user) {
        $user->setApp(1);
        $user->setStep(0);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $random_id = rand(5, 2147483647);

        $this->vk->messages()->send($this->access_token, [
            'user_id' => $user->getVkId(),
            'random_id' => $random_id,
            'message' => "⚠️ Введите дату, время и текст напоминания в формате:\n\nДата (дд-мм-гггг ЧЧ:ММ)\nТекст",
            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
        ]);
    }

    public function runStep($group_id, $secret, $object, $user) {
        if($object['message']['text'] == 'Отмена') {
            $user->setApp(0);
            $user->setStep(0);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $random_id = rand(5, 2147483647);

            $this->vk->messages()->send($this->access_token, [
                'user_id' => $user->getVkId(),
                'random_id' => $random_id,
                'message' => '❎ Создание напоминания отменено',
                'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
            ]);

            return;
        }

        switch($user->getStep()){
            case 0:
                $random_id = rand(5, 2147483647);
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
                            'random_id' => $random_id,
                            'message' => $wrong_date_message,
                            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                        ]);
                    }

                    if($hour > 24 || $hour < 0 || $minute > 60 || $minute < 0) {
                        $this->vk->messages()->send($this->access_token, [
                            'user_id' => $user->getVkId(),
                            'random_id' => $random_id,
                            'message' => $wrong_date_message,
                            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                        ]);
                    }

                    $date_string = sprintf("%'.04d-%'.02d-%'.02d %'.02d:%'.02d", $year, $month, $day, $hour, $minute);
                    $date = strtotime($date_string);
                    $now = time();
                    $datediff = $now - $date;

                    if ($datediff >= 0) {
                        $this->vk->messages()->send($this->access_token, [
                            'user_id' => $user->getVkId(),
                            'random_id' => $random_id,
                            'message' => $wrong_date_message,
                            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
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
                        'random_id' => $random_id,
                        'message' => sprintf("✅ Напоминание успешно создано.\n\nДата и время напоминания: %'.02d.%'.02d.%'.04d в %'.02d:%'.02d\nТекст напоминания: %s", $day, $month, $year, $hour, $minute, $text),
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
                    ]);
                } else {
                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => $random_id,
                        'message' => $wrong_date_message,
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                    ]);
                }
                break;
        }
    }
}   