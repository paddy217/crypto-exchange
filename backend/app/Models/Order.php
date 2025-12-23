<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    const STATUS_OPEN = 1;
    const STATUS_FILLED = 2;
    const STATUS_CANCELLED = 3;

    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'amount' => 'decimal:8',
            'status' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isBuy(): bool
    {
        return $this->side === 'buy';
    }

    public function isSell(): bool
    {
        return $this->side === 'sell';
    }

    public function getTotal(): float
    {
        return bcmul($this->price, $this->amount, 8);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeBySymbol($query, string $symbol)
    {
        return $query->where('symbol', $symbol);
    }

    public function scopeBuys($query)
    {
        return $query->where('side', 'buy');
    }

    public function scopeSells($query)
    {
        return $query->where('side', 'sell');
    }
}
