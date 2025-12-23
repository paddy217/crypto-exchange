<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * This function is used to retirn the order book
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol' => 'required|string|in:BTC,ETH',
        ]);

        $orderbook = $this->orderService->getOrderbook($validated['symbol']);

        return response()->json($orderbook);
    }

    /**
     * This function is used to place a bid
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol' => 'required|string|in:BTC,ETH',
            'side' => 'required|string|in:buy,sell',
            'price' => 'required|numeric|min:0.00000001',
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        try {
            $order = $this->orderService->createOrder($request->user(), $validated);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * This function is used to cancel the bid
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $order = $this->orderService->cancelOrder($order);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * This function is used to return user orders
     */
    public function userOrders(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }
}
