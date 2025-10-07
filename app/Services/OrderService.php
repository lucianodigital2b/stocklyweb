<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMeta;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderCreationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Create a new order.
     *
     * @param array $orderData
     * @param array $orderItems
     * @param array $orderMeta
     * @return Order
     * @throws OrderCreationException
     */
    public function createOrder(array $orderData, array $orderItems, array $orderMeta = []): Order
    {
        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create($orderData);

            // Add order items and deduct stock
            foreach ($orderItems as $item) {
                // Deduct stock for the product or variant
                $this->deductStock(
                    $item['product_id'] ?? null,
                    $item['product_variant_id'] ?? null,
                    $item['quantity']
                );

                // Create the order item
                $order->items()->create($item);
            }

            // Add order meta
            foreach ($orderMeta as $meta) {
                $order->metas()->create($meta);
            }

            DB::commit();

            return $order;
        } catch (InsufficientStockException $e) {
            DB::rollBack();
            Log::error('Insufficient stock for order creation: ' . $e->getMessage());
            throw new OrderCreationException('Insufficient stock for ID: ' . $e->getProductId());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create order: ' . $e->getMessage());
            throw new OrderCreationException('Failed to create order: ' . $e->getMessage());
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
     * @throws OrderCreationException
     */
    public function updateOrder(int $orderId, array $orderData, array $orderItems = [], array $orderMeta = []): Order
    {
        DB::beginTransaction();

        try {
            // Find the order
            $order = Order::findOrFail($orderId);

            // Update order data
            $order->update($orderData);

            // Update or create order items
            foreach ($orderItems as $item) {
                if (isset($item['id'])) {
                    // Update existing item
                    $orderItem = OrderItem::findOrFail($item['id']);
                    $orderItem->update($item);
                } else {
                    // Create new item and deduct stock
                    $this->deductStock(
                        $item['product_id'] ?? null,
                        $item['product_variant_id'] ?? null,
                        $item['quantity']
                    );
                    $order->items()->create($item);
                }
            }

            // Update or create order meta
            foreach ($orderMeta as $meta) {
                if (isset($meta['id'])) {
                    // Update existing meta
                    $orderMeta = OrderMeta::findOrFail($meta['id']);
                    $orderMeta->update($meta);
                } else {
                    // Create new meta
                    $order->metas()->create($meta);
                }
            }

            DB::commit();

            return $order;
        } catch (InsufficientStockException $e) {
            DB::rollBack();
            Log::error('Insufficient stock for order update: ' . $e->getMessage());
            throw new OrderCreationException('Insufficient stock for ID: ' . $e->getProductId());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order: ' . $e->getMessage());
            throw new OrderCreationException('Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Delete an order and restore stock.
     *
     * @param int $orderId
     * @return bool
     * @throws OrderCreationException
     */
    public function deleteOrder(int $orderId): bool
    {
        DB::beginTransaction();

        try {
            // Find the order
            $order = Order::findOrFail($orderId);

            // Restore stock for each item
            foreach ($order->items as $item) {
                $this->restoreStock(
                    $item->product_id,
                    $item->product_variant_id,
                    $item->quantity
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
            throw new OrderCreationException('Failed to delete order: ' . $e->getMessage());
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
        $query = Order::query();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Deduct stock for a product or variant.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @param int $quantity
     * @throws InsufficientStockException
     */
    protected function deductStock(?int $productId, ?int $variantId, int $quantity): void
    {
        // Find the inventory record
        $inventory = $this->findInventory($productId, $variantId);

        if (!$inventory) {
            $id = $variantId ?? $productId;
            throw new InsufficientStockException("Inventory not found for ID: $id", $id);
        }

        if ($inventory->quantity < $quantity) {
            $id = $variantId ?? $productId;
            throw new InsufficientStockException("Insufficient stock for ID: $id", $id);
        }

        // Deduct the stock
        $inventory->quantity -= $quantity;
        $inventory->save();

        // Record the stock movement
        $this->recordStockMovement($productId, $variantId, -$quantity, 'order');
    }

    /**
     * Restore stock for a product or variant.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @param int $quantity
     */
    protected function restoreStock(?int $productId, ?int $variantId, int $quantity): void
    {
        // Find the inventory record
        $inventory = $this->findInventory($productId, $variantId);

        if ($inventory) {
            // Restore the stock
            $inventory->quantity += $quantity;
            $inventory->save();

            // Record the stock movement
            $this->recordStockMovement($productId, $variantId, $quantity, 'order_cancellation');
        }
    }

    /**
     * Find the inventory record for a product or variant.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @return Inventory|null
     */
    protected function findInventory(?int $productId, ?int $variantId): ?Inventory
    {
        if ($variantId) {
            return Inventory::where('product_variant_id', $variantId)->first();
        }

        return Inventory::where('product_id', $productId)->first();
    }

    /**
     * Record a stock movement.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @param int $quantityChange
     * @param string $movementType
     */
    protected function recordStockMovement(?int $productId, ?int $variantId, int $quantityChange, string $movementType): void
    {
        StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity_change' => $quantityChange,
            'movement_type' => $movementType,
        ]);
    }
}