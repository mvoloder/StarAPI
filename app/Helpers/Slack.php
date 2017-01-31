<?php

namespace App\Helpers;

use App\GenericModel;

class Slack
{

    const HIGH_PRIORITY = 'High';
    const MEDIUM_PRIORITY = 'Medium';
    const LOW_PRIORITY = 'Low';

    public function sendSlackPriorityMessage($recipient, $message, $priority)
    {

        if ($priority === self::HIGH_PRIORITY) {
            \SlackChat::message($recipient, $message, $priority);
        } else {
            \SlackChat::message($recipient, $message, $priority);
        }
    }
}
