<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMeta;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Exceptions\InsufficientStockException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    /**
     * Create a new order.
     *
     * @param array $orderData
     * @param array $orderItems
     * @return Order
     * @throws Exception
     */
    public function createOrder(array $orderData): Order
    {
        DB::beginTransaction();

        // dd($orderData);
        try {
            // Calculate total price from order items
            $totalPrice = $this->calculateTotalPrice($orderData['items']);
            
            // Add total_price to order data
            $orderData['total_price'] = $totalPrice;
            
            // Create the order
            $order = Order::create($orderData);

            // Add order items and deduct stock
            foreach ($order['items'] as $item) {
                // Deduct stock for the product using StockService
                $this->stockService->deductStock(
                    $item['product_id'],
                    $item['quantity'],
                    'order',
                    $order->id
                );

                // Create the order item
                $order->items()->create($item);
            }

        
            DB::commit();

            return $order;
        } catch (InsufficientStockException $e) {
            DB::rollBack();
            Log::error('Insufficient stock for order creation: ' . $e->getMessage());
            throw new Exception('Insufficient stock for ID: ' . $e->getProductId());
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            Log::error('Failed to create order: ' . $e->getMessage());
            throw new Exception('Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing order.
     *
     * @param int $orderId
     * @param array $orderData
     * @param array $orderItems
     * @param array $orderMeta
     * @return Order
     * @throws Exception
     */
    public function updateOrder(int $orderId, array $orderData): Order
    {
        DB::beginTransaction();

        try {
            // Find the order
            $order = Order::findOrFail($orderId);

            // Calculate total price from order items if items are provided
            if (!empty($orderItems)) {
                $totalPrice = $this->calculateTotalPrice($orderItems);
                $orderData['total_price'] = $totalPrice;
            }

            // Update order data
            $order->update($orderData);

            // Update or create order items
            foreach ($order['items'] as $item) {
                if (isset($item['id'])) {
                    // Update existing item
                    $orderItem = OrderItem::findOrFail($item['id']);
                    $orderItem->update($item);
                } else {
                    // Create new item and deduct stock using StockService
                    $this->stockService->deductStock(
                        $item['product_id'],
                        $item['quantity'],
                        'order',
                        $order->id
                    );
                    $order->items()->create($item);
                }
            }

          

            DB::commit();

            return $order;
        } catch (InsufficientStockException $e) {
            DB::rollBack();
            Log::error('Insufficient stock for order update: ' . $e->getMessage());
            throw new Exception('Insufficient stock for ID: ' . $e->getProductId());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order: ' . $e->getMessage());
            throw new Exception('Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Delete an order and restore stock.
     *
     * @param int $orderId
     * @return bool
     * @throws Exception
     */
    public function deleteOrder(int $orderId): bool
    {
        DB::beginTransaction();

        try {
            // Find the order
            $order = Order::findOrFail($orderId);

            // Restore stock for each item using StockService
            foreach ($order->items as $item) {
                $this->stockService->restoreStock(
                    $item->product_id,
                    $item->quantity,
                    'order_cancellation',
                    $order->id
                );
            }

            // Delete associated items and meta
            $order->items()->delete();
            $order->metas()->delete();

            // Delete the order
            $order->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete order: ' . $e->getMessage());
            throw new Exception('Failed to delete order: ' . $e->getMessage());
        }
    }

    /**
     * Get an order by ID.
     *
     * @param int $orderId
     * @return Order
     */
    public function getOrder(int $orderId): Order
    {
        return Order::with(['items', 'metas'])->findOrFail($orderId);
    }

    /**
     * Get a paginated list of orders.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrders(array $filters = [], int $perPage = 10)
    {
        $query = Order::with(['customer', 'items']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }



    /**
     * Calculate the total price from order items.
     *
     * @param array $orderItems
     * @return float
     */
    protected function calculateTotalPrice(array $orderItems): float
    {
        $total = 0;
        
        foreach ($orderItems as $item) {
            // Skip null items
            if (!is_array($item)) {
                continue;
            }
            
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 0;
            $total += $price * $quantity;
        }
        
        return $total;
    }


}