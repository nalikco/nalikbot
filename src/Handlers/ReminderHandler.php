<?php
namespace Klassnoenazvanie\Handlers;

use Klassnoenazvanie\Helpers\TimeToMeet;

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
        $user->setStep(1);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $random_id = rand(5, 2147483647);

        $this->vk->messages()->send($this->access_token, [
            'user_id' => $user->getVkId(),
            'random_id' => $random_id,
            'message' => "🔔 Меню напоминаний",
            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getReminderMainMenu()
        ]);
    }

    public function runStep($group_id, $secret, $object, $user) {
        switch($user->getStep()){
            case 0:
                if($object['message']['text'] == 'Отмена') {
                    $user->setApp(1);
                    $user->setStep(1);
        
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
        
                    $random_id = rand(5, 2147483647);
        
                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => $random_id,
                        'message' => '❎ Создание напоминания отменено',
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getReminderMainMenu()
                    ]);
        
                    return;
                }

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
            case 1:
                if ($object['message']['text'] == 'Создать') {
                    $user->setApp(1);
                    $user->setStep(0);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => "⚠️ Введите дату, время и текст напоминания в формате:\n\nДата (дд-мм-гггг ЧЧ:ММ)\nТекст",
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                    ]);

                    return;
                }

                if ($object['message']['text'] == 'Список активных') {
                    $message = "Список активных напоминаний:\n\n";

                    $activeReminders = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->findBy(['done' => 0]);

                    foreach($activeReminders as $reminder) {
                        $now = time();
                        $datediff = $now - $reminder->getDate()->getTimestamp();

                        if ($datediff < 0){
                            $today = new \DateTime("today");

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
                                    $formatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
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

                                $timeToDate = TimeToMeet::num_word($days, ['день', 'дня', 'дней']);
                            } else $timeToDate = TimeToMeet::num_word($hours, ['час', 'часа', 'часов']);

                            $userInfo = $this->vk->users()->get($this->access_token, ['user_id' => $user->getVkId()])[0];

                            $message = $message."\n— ".$date.":\nТекст: ".$reminder->getText()."\n@id".$userInfo['id']." (".$userInfo['first_name']."), ID ".$reminder->getId()." (через ".$timeToDate.")\n";
                        }
                    }

                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => $message,
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getReminderMainMenu()
                    ]);

                    return;
                }

                if($object['message']['text'] == 'Вернуться') {
                    $user->setApp(0);
                    $user->setStep(0);
        
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
        
                    $random_id = rand(5, 2147483647);
        
                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => $random_id,
                        'message' => '🔄 Возврат к главному меню',
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
                    ]);
        
                    return;
                }

                if ($object['message']['text'] == 'Удалить') {
                    $user->setApp(1);
                    $user->setStep(2);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => rand(5, 2147483647),
                        'message' => "⚠️ Введите ID напоминания",
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                    ]);

                    return;
                }
                break;
            case 2:
                $random_id = rand(5, 2147483647);

                if($object['message']['text'] == 'Отмена') {
                    $user->setApp(1);
                    $user->setStep(1);
        
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
        
                    $random_id = rand(5, 2147483647);
        
                    $this->vk->messages()->send($this->access_token, [
                        'user_id' => $user->getVkId(),
                        'random_id' => $random_id,
                        'message' => '❎ Удаление напоминания отменено',
                        'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getReminderMainMenu()
                    ]);
        
                    return;
                }

                $reminder_id = intval($object['message']['text']);

                if ($reminder_id == 0) return $this->vk->messages()->send($this->access_token, [
                    'user_id' => $user->getVkId(),
                    'random_id' => $random_id,
                    'message' => '❎ Некорректный ID напоминания',
                    'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                ]);

                $reminder = $this->entityManager->getRepository('Klassnoenazvanie\Reminder')->findOneBy(['id' => $reminder_id, 'user' => $user, 'done' => 0]);

                if (!$reminder) return $this->vk->messages()->send($this->access_token, [
                    'user_id' => $user->getVkId(),
                    'random_id' => $random_id,
                    'message' => '❎ Напоминания с таким ID не существует',
                    'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getWithCancel()
                ]);

                $reminder->setDone(1);
                $user->setApp(0);
                $user->setStep(0);
    
                $this->entityManager->persist($user);
                $this->entityManager->persist($reminder);
                $this->entityManager->flush();

                return $this->vk->messages()->send($this->access_token, [
                    'user_id' => $user->getVkId(),
                    'random_id' => $random_id,
                    'message' => '🗑 Напоминание успешно удалено',
                    'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
                ]);

                break;
        }
    }
}   