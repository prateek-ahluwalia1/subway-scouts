<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $name;
    public $subject;
    public $send_to;
    public $ticketCount;
    public function __construct($name, $subject, $send_to, $ticketCount)
    {
        $this->name = $name;
        $this->subject = $subject;
        $this->send_to = $send_to;
        $this->ticketCount = $ticketCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('ticket-management');
    }
    public function broadcastAs() {
        return 'ticket-management';
    }
}
