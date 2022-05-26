<?php
namespace Klassnoenazvanie\Handlers;

use VK\CallbackApi\VKCallbackApiHandler;

class Handler extends VKCallbackApiHandler {
    private $vk;
    private $access_token;
    private $entityManager;
    private $apps;

    private $coursesHandler;
    private $reminderHandler;
    private $statsHandler;

    public function __construct($vk, $access_token, $entityManager) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->entityManager = $entityManager;

        $this->coursesHandler = new CoursesHandler($vk, $access_token, $entityManager);
        $this->reminderHandler = new ReminderHandler($vk, $access_token, $entityManager);
        $this->statsHandler = new StatsHandler($vk, $access_token, $entityManager);
        
        $this->apps = $this->getApps();
    }

    public function messageNew($group_id, $secret, $object) {
        $from_id = $object['message']['from_id'];
        if ($from_id != $_ENV['OKSY_ID'] && $from_id != $_ENV['IGOR_ID']) return;

        $user = $this->entityManager->getRepository('Klassnoenazvanie\User')->findOneBy(['vkid' => $from_id]);

        if(!$user){
            $user = new \Klassnoenazvanie\User();
            $user->setVkId($from_id);
            $user->setApp(0);
            $user->setStep(0);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $userApp = $user->getApp();
        if($userApp != 0) {
            $this->apps[$userApp]->runStep($group_id, $secret, $object, $user);
        } else {
            if ($object['message']['text'] == 'Курсы валют') $this->coursesHandler->getCourses($group_id, $secret, $object, $user);
            if ($object['message']['text'] == 'Напоминания') $this->reminderHandler->initiate($group_id, $secret, $object, $user);
            if ($object['message']['text'] == 'Статистика') $this->statsHandler->getStats($group_id, $secret, $object, $user);
        }
    }

    private function getApps() {
        return [
            1 => $this->reminderHandler
        ];
    }
}   