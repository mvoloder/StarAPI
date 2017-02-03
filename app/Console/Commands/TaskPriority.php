<?php

namespace App\Console\Commands;

use App\Helpers\Slack;
use Illuminate\Console\Command;
use App\GenericModel;

class TaskPriority extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slackMessage:send {priority}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron that sends task priority slack messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $priority = \Config::get('sharedSettings.internalConfiguration.priorityMapping');

        $slack = new Slack();

        $map = $this->argument('priority');

        $recipient = '@mvoloder';
        $message = ' Priority!!';

        GenericModel::setCollection('slackMessages');

        if (intval($map) === 0) {
            $slack->sendMessage($recipient, 'High' . $message, $priority);
        } elseif (intval($map) === 30) {
            $slackMessage = GenericModel::create();
            $slackMessage->recipient = $recipient;
            $slackMessage->message = 'Medium '. $message;
            $slackMessage->priority = $map;
            $slackMessage->sent = false;
            $slackMessage->save();

            sleep(5); //1800
            $slack->sendMessage($recipient, $slackMessage->message, $priority);
            $slackMessage->sent = true;
            $slackMessage->save();
        } elseif (intval($map) === 120) {
            $slackMessage = GenericModel::create();
            $slackMessage->recipient = $recipient;
            $slackMessage->message = 'Low' . $message;
            $slackMessage->priority = $map;
            $slackMessage->sent = false;
            $slackMessage->save();

            sleep(10); //7200
            $slack->sendMessage($recipient, $slackMessage->message, $priority);
            $slackMessage->sent = true;
            $slackMessage->save();
        }
    }
}
