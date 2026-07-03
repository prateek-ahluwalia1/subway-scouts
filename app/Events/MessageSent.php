<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $guard_id;
    public $admin_id;
    public $customer_id;
    public $contractor_id;
    public $saleperson_id;
    public $send_by;
    public $user_name;
    public function __construct($guard_id, $admin_id, $customer_id, $contractor_id, $saleperson_id, $send_by, $user_name)
    {
        $this->guard_id = $guard_id;
        $this->admin_id = $admin_id;
        $this->customer_id = $customer_id;
        $this->contractor_id = $contractor_id;
        $this->saleperson_id = $saleperson_id;
        $this->send_by = $send_by;
        $this->user_name = $user_name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chat-channel');
    }
    public function broadcastAs() {
        return 'chat-channel';
    }
}
