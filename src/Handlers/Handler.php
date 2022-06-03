<?php
namespace Klassnoenazvanie\Handlers;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Klassnoenazvanie\Helpers\Keyboards;
use VK\CallbackApi\VKCallbackApiHandler;
use Klassnoenazvanie\User;
use Doctrine\ORM\EntityManager;
use VK\Client\VKApiClient;

class Handler extends VKCallbackApiHandler {
    private EntityManager $entityManager;
    private array $apps;

    private VKApiClient $vk;
    private string $accessToken;


    private CoursesHandler $coursesHandler;
    private ReminderHandler $reminderHandler;
    private StatsHandler $statsHandler;

    public function __construct($vk, $accessToken, $entityManager) {
        $this->entityManager = $entityManager;
        $this->vk = $vk;
        $this->accessToken = $accessToken;


        $this->coursesHandler = new CoursesHandler($vk, $accessToken);
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
        $user = $this->getUser($from_id);

        $userApp = $user->getApp();
        if($userApp != 0) $this->apps[$userApp]->runStep($object, $user);
        else if ($object['message']['text'] == 'Меню') $this->vk->messages()->send($this->accessToken, [
            'user_id' => $user->getVkId(),
            'random_id' => rand(5, 2147483647),
            'message' => 'Меню',
            'keyboard' => Keyboards::getMainMenu()
        ]);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function messageEvent($group_id, $secret, $object) {
        $from_id = $object['user_id'];
        if ($from_id != getenv('OKSY_ID') && $from_id != getenv('IGOR_ID')) return;
        $user = $this->getUser($from_id);

        switch($object['payload']['action']){
            case 'get_stats':
                $this->statsHandler->getStats($user, $object['conversation_message_id']);
                break;
            case 'get_courses':
                $this->coursesHandler->getCourses($user, $object['conversation_message_id']);
                break;
            case 'get_reminders_menu':
                $this->reminderHandler->initiate($user, $object['conversation_message_id']);
                break;
            case 'reminder_create':
                $this->reminderHandler->initiateCreating($user, $object['conversation_message_id']);
                break;
            case 'reminder_list_active':
                $this->reminderHandler->listActive($user, $object['conversation_message_id']);
                break;
            case 'reminder_delete':
                $this->reminderHandler->initiateDeleting($user, $object['conversation_message_id']);
                break;

            case 'return':
                $this->vk->messages()->edit($this->accessToken, [
                    'peer_id' => $user->getVkId(),
                    'message' => 'Меню',
                    'conversation_message_id' => $object['conversation_message_id'],
                    'keyboard' => Keyboards::getMainMenu()
                ]);
                break;
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function getUser(int $userId): User
    {
        $user = $this->entityManager->getRepository('Klassnoenazvanie\User')->findOneBy(['vkid' => $userId]);

        if(!$user){
            $user = new User();
            $user->setVkId($userId);
            $user->setApp(0);
            $user->setStep(0);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }

    private function getApps(): array
    {
        return [
            1 => $this->reminderHandler
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function parseObject(int $group_id, ?string $secret, string $type, array $object) {
        switch ($type) {
            case static::CALLBACK_EVENT_MESSAGE_NEW:
                $this->messageNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MESSAGE_REPLY:
                $this->messageReply($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MESSAGE_ALLOW:
                $this->messageAllow($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MESSAGE_DENY:
                $this->messageDeny($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_PHOTO_NEW:
                $this->photoNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_PHOTO_COMMENT_NEW:
                $this->photoCommentNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_PHOTO_COMMENT_EDIT:
                $this->photoCommentEdit($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_PHOTO_COMMENT_RESTORE:
                $this->photoCommentRestore($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_PHOTO_COMMENT_DELETE:
                $this->photoCommentDelete($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_AUDIO_NEW:
                $this->audioNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_VIDEO_NEW:
                $this->videoNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_VIDEO_COMMENT_NEW:
                $this->videoCommentNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_VIDEO_COMMENT_EDIT:
                $this->videoCommentEdit($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_VIDEO_COMMENT_RESTORE:
                $this->videoCommentRestore($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_VIDEO_COMMENT_DELETE:
                $this->videoCommentDelete($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_WALL_POST_NEW:
                $this->wallPostNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_WALL_REPOST:
                $this->wallRepost($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_WALL_REPLY_NEW:
                $this->wallReplyNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_WALL_REPLY_EDIT:
                $this->wallReplyEdit($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_WALL_REPLY_RESTORE:
                $this->wallReplyRestore($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_WALL_REPLY_DELETE:
                $this->wallReplyDelete($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_BOARD_POST_NEW:
                $this->boardPostNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_BOARD_POST_EDIT:
                $this->boardPostEdit($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_BOARD_POST_RESTORE:
                $this->boardPostRestore($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_BOARD_POST_DELETE:
                $this->boardPostDelete($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MARKET_COMMENT_NEW:
                $this->marketCommentNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MARKET_COMMENT_EDIT:
                $this->marketCommentEdit($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MARKET_COMMENT_RESTORE:
                $this->marketCommentRestore($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_MARKET_COMMENT_DELETE:
                $this->marketCommentDelete($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_GROUP_LEAVE:
                $this->groupLeave($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_GROUP_JOIN:
                $this->groupJoin($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_GROUP_CHANGE_SETTINGS:
                $this->groupChangeSettings($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_GROUP_CHANGE_PHOTO:
                $this->groupChangePhoto($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_GROUP_OFFICERS_EDIT:
                $this->groupOfficersEdit($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_POLL_VOTE_NEW:
                $this->pollVoteNew($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_USER_BLOCK:
                $this->userBlock($group_id, $secret, $object);
                break;
            case static::CALLBACK_EVENT_USER_UNBLOCK:
                $this->userUnblock($group_id, $secret, $object);
                break;
            case "message_event":
                $this->messageEvent($group_id, $secret, $object);
                break;
        }
    }
}   