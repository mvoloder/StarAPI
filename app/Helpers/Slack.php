<?php

namespace App\Helpers;

use App\GenericModel;

class Slack
{

    const HIGH_PRIORITY = 1;
    const MEDIUM_PRIORITY = 2;
    const LOW_PRIORITY = 3;

    public function sendSlackPriorityMessage($recipient, $message, $priority)
    {
        print_r($priority);
        if ($priority !== self::HIGH_PRIORITY) {
            \SlackChat::message($recipient, $message, $priority);
        } elseif ($priority === self::HIGH_PRIORITY) {
            \SlackChat::message($recipient, $message, $priority);
        }
    }
}
