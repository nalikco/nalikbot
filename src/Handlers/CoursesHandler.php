<?php
namespace Klassnoenazvanie\Handlers;

class CoursesHandler {
    private $vk;
    private $access_token;
    private $entityManager;

    public function __construct($vk, $access_token, $entityManager) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->entityManager = $entityManager;
    }

    public function getCourses($group_id, $secret, $object, $user) {
        $random_id = rand(5, 2147483647);

        $message_id = $this->vk->messages()->send($this->access_token, [
            'user_id' => $user->getVkId(),
            'random_id' => $random_id,
            'message' => 'Получение курса...',
            'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::clear()
        ]);

        $courses_url = 'https://belarusbank.by/api/kursExchange?city=%D0%9C%D0%B8%D0%BD%D1%81%D0%BA';

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $courses_url);
            $result = curl_exec($curl);
            curl_close($curl);
            $courses = json_decode($result)[0];
            
            $message = "💵 USD — ".number_format(floatval($courses->USD_out), 2, '.')." бел. руб.\n💶 EUR — ".number_format(floatval($courses->EUR_out), 2, '.')." бел. руб.";

            $this->vk->messages()->edit($this->access_token, [
                'peer_id' => $user->getVkId(),
                'message' => $message,
                'message_id' => $message_id,
                'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
            ]);
        } catch (Exception $e) {
            $this->vk->messages()->edit($this->access_token, [
                'peer_id' => $user->getVkId(),
                'message' => 'Ошибка получения курса. Попробуйте ещё раз.',
                'message_id' => $message_id,
                'keyboard' => \Klassnoenazvanie\Helpers\Keyboards::getMain()
            ]);
        }
    }
}   