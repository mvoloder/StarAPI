<?php

namespace App\Events;

use App\Events\Event;
use App\GenericModel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TaskUpdateSlackNotify extends Event
{
    use SerializesModels;

    public $tasks;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(GenericModel $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}