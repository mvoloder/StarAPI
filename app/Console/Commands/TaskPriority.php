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

        $message = [];
        $recipient = ['@mvoloder'];
        $highPriorityTask = [];
        $mediumPriorityTask = [];
        $lowPriorityTask = [];
        $priority = \Config::get('sharedSettings.internalConfiguration.taskPriorities');

        $slack = new Slack();
        GenericModel::setCollection('slackMessages');
        $slackMessage = GenericModel::create();

        foreach ($tasks as $task) {
            if (empty($task->owner) && ($task->priority === 'High')) {
                    $highPriorityTask[] = $task->id;
                    $message[] = "High priority task : " . $task->id;
                    $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
            if (empty($task->owner) && ($task->priority === 'Medium')) {
                $mediumPriorityTask[] = $task->id;
                $slackMessage->mediumPriority = "Medium priority task : " . $task->id;
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
            if (empty($task->owner) && ($task->priority === 'Low')) {
                $lowPriorityTask[] = $task->id;
                $slackMessage->lowPriority = "Low priority task : " . $task->id;
                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
            }
        }

//        if ($priority ==! 'High'){
//            foreach ($tasks as $task){
//                if (empty($task->owner)){
//                    $unassignedTasks[$task->id] = $task->priority;
//                }
//            }
//
//            GenericModel::setCollection('slackMessages');
//            $slackMessages = GenericModel::create();
//
//            foreach ($unassignedTasks as $key => $value){
//                if ($value === \Config::get('sharedSettings.taskPriority.Medium')){
//                    $slackMessages->mediumPriority = "Medium priority task : " . $key;
//                    $message[$value] = $key;
//                }elseif ($value === \Config::get('sharedSettings.taskPriority.Low')){
//                    $slackMessages->lowPriority = "Low priority task : " . $key;
//                    $message[$value] = $key;
//                }
//                $slackMessages->save();
//            }
//            $slack->sendSlackPriorityMessage($recipient, $message, $priority);
//        }
//
//        if ($priority === 'High'){
//            foreach ($tasks as $task){
//                if (empty($task->owner) && $cronTime === \Config::get('sharedSettings.priorityMapping.1')){
//                    $message = "High priority task : " . $task->id;
//                }
//                $slack->sendSlackPriorityMessage($recipient, $message, $priority);
//            }
//        }
    }
}
