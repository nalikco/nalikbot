<?php
namespace Klassnoenazvanie\Handlers;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use VK\CallbackApi\VKCallbackApiHandler;
use \Klassnoenazvanie\User;
use Doctrine\ORM\EntityManager;

class Handler extends VKCallbackApiHandler {
    private EntityManager $entityManager;
    private array $apps;

    private CoursesHandler $coursesHandler;
    private ReminderHandler $reminderHandler;
    private StatsHandler $statsHandler;

    public function __construct($vk, $accessToken, $entityManager) {
        $this->entityManager = $entityManager;

        $this->coursesHandler = new CoursesHandler($vk, $accessToken, $entityManager);
        $this->reminderHandler = new ReminderHandler($vk, $accessToken, $entityManager);
        $this->statsHandler = new StatsHandler($vk, $accessToken, $entityManager);
        
        $this->apps = $this->getApps();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function messageNew($group_id, $secret, $object) {
        $from_id = $object['message']['from_id'];
        if ($from_id != getenv('OKSY_ID') && $from_id != getenv('IGOR_ID')) return;

        $user = $this->entityManager->getRepository('Klassnoenazvanie\User')->findOneBy(['vkid' => $from_id]);

        if(!$user){
            $user = new User();
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

    private function getApps(): array
    {
        return [
            1 => $this->reminderHandler
        ];
    }
}   