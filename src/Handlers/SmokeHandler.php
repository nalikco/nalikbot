<?php
namespace Klassnoenazvanie\Handlers;

use VK\CallbackApi\VKCallbackApiHandler;

class SmokeHandler extends VKCallbackApiHandler {
    public function messageNew($group_id, $secret, $object) { 
        echo $object;
    }
}