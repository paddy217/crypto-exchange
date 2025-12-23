<?php

namespace App\Events;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Trade $trade;
    public User $user;

    public function __construct(Trade $trade, User $user)
    {
        $this->trade = $trade;
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    public function broadcastWith(): array
    {
        $isBuyer = $this->trade->buyer_id === $this->user->id;

        return [
            'trade' => [
                'id' => $this->trade->id,
                'symbol' => $this->trade->symbol,
                'price' => $this->trade->price,
                'amount' => $this->trade->amount,
                'total' => $this->trade->total,
                'commission' => $this->trade->commission,
                'side' => $isBuyer ? 'buy' : 'sell',
                'created_at' => $this->trade->created_at->toISOString(),
            ],
            'user' => [
                'id' => $this->user->id,
                'balance' => $this->user->fresh()->balance,
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.matched';
    }
}
