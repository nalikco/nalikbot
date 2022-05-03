<?php
namespace Klassnoenazvanie\Handlers;

use VK\CallbackApi\VKCallbackApiHandler;

class Handler extends VKCallbackApiHandler {
    private $vk;
    private $access_token;
    private $coursesHandler;

    public function __construct($vk, $access_token) {
        $this->vk = $vk;
        $this->access_token = $access_token;
        $this->coursesHandler = new CoursesHandler($vk, $access_token);
    }

    public function messageNew($group_id, $secret, $object) {
        $from_id = $object['message']['from_id'];
        if ($from_id != $_ENV['OKSY_ID'] && $from_id != $_ENV['IGOR_ID']) return;

        if ($object['message']['text'] == 'Курсы валют') $this->coursesHandler->getCourses($group_id, $secret, $object, $from_id);
    }
}   