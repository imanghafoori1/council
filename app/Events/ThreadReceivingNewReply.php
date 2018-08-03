<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ThreadReceivingNewReply
{
    use Dispatchable, SerializesModels;

    /**
     * The reply that was posted.
     *
     * @param \App\Thread $reply
     */
    public $thread;

    /**
     * Create a new event instance.
     *
     * @param \App\Thread $thread
     */
    public function __construct($thread)
    {
        $this->thread = $thread;
    }

    /**
     * Get the subject of the event.
     */
    public function subject()
    {
        return $this->thread;
    }
}
