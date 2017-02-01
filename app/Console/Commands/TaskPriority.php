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
        GenericModel::setCollection('tasks');
        $tasks = GenericModel::all();

        $priority = \Config::get('sharedSettings.internalConfiguration.taskPriorities');

        $priorityMapping = \Config::get('sharedSettings.internalConfiguration.priorityMapping');

        $recipient = '@mvoloder';
        $highPriority = [];
        $mediumPriority = [];
        $lowPriority = [];

        foreach ($tasks as $task) {
            if (empty($task->owner) && ($task->priority === $priority[0])) {
                $highPriority[] = "High priority task : " . $task->id;
            }
            if (empty($task->owner) && ($task->priority === $priority[1])) {
                $mediumPriority[] = "Medium priority task : " . $task->id;
            }
            if (empty($task->owner) && ($task->priority === $priority[2])) {
                $lowPriority[] = "Low priority task : " . $task->id;
            }
        }

        $slack = new Slack();
        GenericModel::setCollection('slackMessages');

        $p = $this->argument('priority');


        if (intval($p) === intval($priorityMapping['High'])) {
            foreach ($highPriority as $message) {
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
        }

        if (intval($p) === intval($priorityMapping['Medium'])) {
            foreach ($mediumPriority as $message) {
                $slackMessage = GenericModel::create();
                $slackMessage->mediumPriority = $message;
                $slackMessage->save();
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
        }

        if (intval($p) === intval($priorityMapping['Low'])) {
            foreach ($lowPriority as $message) {
                $slackMessage = GenericModel::create();
                $slackMessage->lowPriority = $message;
                $slackMessage->save();
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
        }
    }
}
