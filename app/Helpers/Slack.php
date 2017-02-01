<?php

namespace App\Helpers;

class Slack
{

    const HIGH_PRIORITY = 'High';
    const MEDIUM_PRIORITY = 'Medium';
    const LOW_PRIORITY = 'Low';

    public function sendSlackPriorityMessage($recipient, $message, $priority)
    {
        \SlackChat::message($recipient, $message, $priority);
    }
}
