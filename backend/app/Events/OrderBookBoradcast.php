<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderBookBoradcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orderBook'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'isOrderBookUpdated' => true,
        ];
    }

    public function broadcastAs(): string
    {
        return 'orderbook.updated';
    }
}
