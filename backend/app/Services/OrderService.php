<?php

namespace App\Services;

use App\Events\OrderMatched;
use App\Events\OrderBookBoradcast;
use App\Models\Asset;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    const COMMISSION_RATE = 0.015;

    /**
     * This function is used to create an order
     */
    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $symbol = strtoupper($data['symbol']);
            $side = $data['side'];
            $price = $data['price'];
            $amount = $data['amount'];
            $total = bcmul($price, $amount, 8);

            if ($side === 'buy') {
                $this->validateAndLockBuyOrder($user, $total);
            } else {
                $this->validateAndLockSellOrder($user, $symbol, $amount);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'side' => $side,
                'price' => $price,
                'amount' => $amount,
                'status' => Order::STATUS_OPEN,
            ]);

            $this->tryMatchOrder($order);

            $this->broadcastOrderBook();

            return $order->fresh();
        });
    }

    /**
     * This function is used to validate the buy order
     */
    protected function validateAndLockBuyOrder(User $user, string $total): void
    {
        if (bccomp($user->balance, $total, 8) < 0) {
            throw new \Exception('Insufficient USD balance');
        }

        $user->balance = bcsub($user->balance, $total, 8);
        $user->save();
    }

    /**
     * This function is used to validate the sell order
     */
    protected function validateAndLockSellOrder(User $user, string $symbol, string $amount): void
    {
        $asset = $user->assets()->where('symbol', $symbol)->first();

        if (!$asset || bccomp($asset->amount, $amount, 8) < 0) {
            throw new \Exception('Insufficient asset balance');
        }

        $asset->amount = bcsub($asset->amount, $amount, 8);
        $asset->locked_amount = bcadd($asset->locked_amount, $amount, 8);
        $asset->save();
    }

    /**
     * This function is used to cancel the order
     */
    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            if (!$order->isOpen()) {
                throw new \Exception('Order is not open');
            }

            $user = $order->user;

            if ($order->isBuy()) {
                $total = bcmul($order->price, $order->amount, 8);
                $user->balance = bcadd($user->balance, $total, 8);
                $user->save();
            } else {
                $asset = $user->assets()->where('symbol', $order->symbol)->first();
                if ($asset) {
                    $asset->locked_amount = bcsub($asset->locked_amount, $order->amount, 8);
                    $asset->amount = bcadd($asset->amount, $order->amount, 8);
                    $asset->save();
                }
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            $this->broadcastOrderBook();

            return $order;
        });
    }

    /**
     * This function is used to match the order
     */
    protected function tryMatchOrder(Order $order): void
    {
        if ($order->isBuy()) {
            $matchingOrder = Order::open()
                ->bySymbol($order->symbol)
                ->sells()
                ->where('price', '=', $order->price)
                ->where('amount', '=', $order->amount)
                ->where('user_id', '!=', $order->user_id)
                ->orderBy('price', 'asc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();
        } else {
            $matchingOrder = Order::open()
                ->bySymbol($order->symbol)
                ->buys()
                ->where('price', '=', $order->price)
                ->where('amount', '=', $order->amount)
                ->where('user_id', '!=', $order->user_id)
                ->orderBy('price', 'desc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();
        }

        if ($matchingOrder) {
            $this->executeMatch($order, $matchingOrder);
        }
    }

    /**
     * This function is used to execute the match
     */
    protected function executeMatch(Order $newOrder, Order $existingOrder): void
    {
        $buyOrder = $newOrder->isBuy() ? $newOrder : $existingOrder;
        $sellOrder = $newOrder->isSell() ? $newOrder : $existingOrder;

        $executionPrice = $existingOrder->price;
        $amount = $newOrder->amount;
        $total = bcmul($executionPrice, $amount, 8);
        $commission = bcmul($total, (string) self::COMMISSION_RATE, 8);

        $buyer = $buyOrder->user;
        $seller = $sellOrder->user;

        // Handle price difference refund for buyer if execution price is lower
        if ($buyOrder === $newOrder) {
            $lockedTotal = bcmul($buyOrder->price, $buyOrder->amount, 8);
            $refund = bcsub($lockedTotal, $total, 8);
            if (bccomp($refund, '0', 8) > 0) {
                $buyer->balance = bcadd($buyer->balance, $refund, 8);
            }
        }

        // Deduct commission from buyer
        $buyerReceives = $amount;
        $sellerReceives = bcsub($total, $commission, 8);

        // Transfer asset to buyer
        $buyerAsset = Asset::firstOrCreate(
            ['user_id' => $buyer->id, 'symbol' => $buyOrder->symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );
        $buyerAsset->amount = bcadd($buyerAsset->amount, $buyerReceives, 8);
        $buyerAsset->save();

        // Release locked asset from seller and transfer USD
        $sellerAsset = $seller->assets()->where('symbol', $sellOrder->symbol)->first();
        if ($sellerAsset) {
            $sellerAsset->locked_amount = bcsub($sellerAsset->locked_amount, $amount, 8);
            $sellerAsset->save();
        }

        $seller->balance = bcadd($seller->balance, $sellerReceives, 8);
        $seller->save();
        $buyer->save();

        // Update order statuses
        $newOrder->status = Order::STATUS_FILLED;
        $newOrder->save();
        $existingOrder->status = Order::STATUS_FILLED;
        $existingOrder->save();

        // Create trade record
        $trade = Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'symbol' => $buyOrder->symbol,
            'price' => $executionPrice,
            'amount' => $amount,
            'total' => $total,
            'commission' => $commission,
        ]);

        try {
            // Broadcast to both parties
            event(new OrderMatched($trade, $buyer));
            event(new OrderMatched($trade, $seller));

            $this->broadcastOrderBook();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * This function is used to get the order book
     */
    public function getOrderbook(string $symbol): array
    {
        $symbol = strtoupper($symbol);

        $buyOrders = Order::open()
            ->bySymbol($symbol)
            ->buys()
            ->orderBy('price', 'desc')
            ->get(['id', 'price', 'amount', 'created_at'])
            ->toArray();

        $sellOrders = Order::open()
            ->bySymbol($symbol)
            ->sells()
            ->orderBy('price', 'asc')
            ->get(['id', 'price', 'amount', 'created_at'])
            ->toArray();

        return [
            'buy' => $buyOrders,
            'sell' => $sellOrders,
        ];
    }

    public function broadcastOrderBook()
    {
        event(new OrderBookBoradcast());
    }
}
