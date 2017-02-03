<?php

namespace App\Helpers;

use App\GenericModel;

class Slack
{

    const HIGH_PRIORITY = 0;
    const MEDIUM_PRIORITY = 30;
    const LOW_PRIORITY = 120;

    public function sendMessage($recipient, $message, $priority)
    {
        \SlackChat::message($recipient, $message, $priority);
    }
}
