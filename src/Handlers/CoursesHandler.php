<?php
namespace Klassnoenazvanie\Handlers;

use Exception;
use VK\Client\VKApiClient;

class CoursesHandler {
    private VKApiClient $vk;
    private string $accessToken;

    public function __construct($vk, $accessToken) {
        $this->vk = $vk;
        $this->accessToken = $accessToken;
    }

    public function getCourses($user, int $conversation_message_id): void
    {
        $this->vk->messages()->edit($this->accessToken, [
            'peer_id' => $user->getVkId(),
            'message' => 'Получение курса...',
            'conversation_message_id' => $conversation_message_id
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

            $this->vk->messages()->edit($this->accessToken, [
                'peer_id' => $user->getVkId(),
                'message' => $message,
                'conversation_message_id' => $conversation_message_id
            ]);
        } catch (Exception $e) {
            $this->vk->messages()->edit($this->accessToken, [
                'peer_id' => $user->getVkId(),
                'message' => 'Ошибка получения курса. Попробуйте ещё раз.',
                'conversation_message_id' => $conversation_message_id
            ]);
        }
    }
}   