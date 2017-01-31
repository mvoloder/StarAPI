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
    protected $signature = 'slackMessage:send';

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

        $date = new \DateTime();
        $cronTime = $date->format('U');

        $priorityMapping = \Config::get('sharedSettings.internalConfiguration.priorityMapping');

        $message = [];
        $recipient = ['@mvoloder'];
        $highPriorityTask = [];
        $mediumPriorityTask = [];
        $lowPriorityTask = [];

        $priority = \Config::get('sharedSettings.internalConfiguration.taskPriorities');

        $slack = new Slack();
        GenericModel::setCollection('slackMessages');

        foreach ($tasks as $task) {
            if (empty($task->owner) && ($task->priority === 'High')) {
                $highPriorityTask[] = $task->id;
                $message[] = "High priority task : " . $task->id;
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
            if (empty($task->owner) && ($task->priority === 'Medium')) {
                $slackMessage = GenericModel::create();
                $mediumPriorityTask[] = $task->id;
                $slackMessage->mediumPriorityTask = $task->id;
                $slackMessage->save();
                $message[] = "Medium priority task : " . $task->id;
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
            if (empty($task->owner) && ($task->priority === 'Low')) {
                $slackMessage = GenericModel::create();
                $lowPriorityTask[] = $task->id;
                $slackMessage->lowPriority = $task->id;
                $slackMessage->save();
                $message[] = "Low priority task : " . $task->id;
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
        }
    }
}
