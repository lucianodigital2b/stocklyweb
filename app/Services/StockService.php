<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Exceptions\InsufficientStockException;

class StockService
{
    /**
     * Deduct stock for a product and record the movement.
     *
     * @param int $productId
     * @param int $quantity
     * @param string $movementType
     * @param int|null $referenceId
     * @throws InsufficientStockException
     */
    public function deductStock(int $productId, int $quantity, string $movementType = 'order', ?int $referenceId = null): void
    {
        // Find the inventory record
        $inventory = $this->findInventory($productId);

        if (!$inventory) {
            throw new InsufficientStockException("Inventory not found for product ID: $productId", $productId);
        }

        if (!$inventory->is_infinite && $inventory->stock < $quantity) {
            throw new InsufficientStockException("Insufficient stock for product ID: $productId", $productId);
        }

        // Store the stock before the change
        $stockBefore = $inventory->stock;
        $isInfiniteBefore = $inventory->is_infinite;

        // Deduct the stock (only if not infinite)
        if (!$inventory->is_infinite) {
            $inventory->stock -= $quantity;
            $inventory->save();
        }

        // Record the stock movement
        $this->recordStockMovement(
            $inventory->id,
            -$quantity,
            $movementType,
            $stockBefore,
            $inventory->stock,
            $isInfiniteBefore,
            $inventory->is_infinite,
            $referenceId
        );
    }

    /**
     * Restore stock for a product and record the movement.
     *
     * @param int $productId
     * @param int $quantity
     * @param string $movementType
     * @param int|null $referenceId
     */
    public function restoreStock(int $productId, int $quantity, string $movementType = 'order_cancellation', ?int $referenceId = null): void
    {
        // Find the inventory record
        $inventory = $this->findInventory($productId);

        if ($inventory) {
            // Store the stock before the change
            $stockBefore = $inventory->stock;
            $isInfiniteBefore = $inventory->is_infinite;

            // Restore the stock (only if not infinite)
            if (!$inventory->is_infinite) {
                $inventory->stock += $quantity;
                $inventory->save();
            }

            // Record the stock movement
            $this->recordStockMovement(
                $inventory->id,
                $quantity,
                $movementType,
                $stockBefore,
                $inventory->stock,
                $isInfiniteBefore,
                $inventory->is_infinite,
                $referenceId
            );
        }
    }

    /**
     * Record a stock movement for a product.
     *
     * @param int $inventoryId
     * @param int $quantityChange
     * @param string $movementType
     * @param int $stockBefore
     * @param int $stockAfter
     * @param bool $isInfiniteBefore
     * @param bool $isInfiniteAfter
     * @param int|null $referenceId
     */
    protected function recordStockMovement(
        int $inventoryId,
        int $quantityChange,
        string $movementType,
        int $stockBefore,
        int $stockAfter,
        bool $isInfiniteBefore,
        bool $isInfiniteAfter,
        ?int $referenceId = null
    ): void {
        StockMovement::create([
            'inventory_id' => $inventoryId,
            'movement_type' => $movementType,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'is_infinite_before' => $isInfiniteBefore,
            'is_infinite_after' => $isInfiniteAfter,
            'user_id' => auth()->id() ?? 1, // Default to user ID 1 if no authenticated user
        ]);
    }

    /**
     * Find the inventory record for a product.
     *
     * @param int $productId
     * @return Inventory|null
     */
    protected function findInventory(int $productId): ?Inventory
    {
        return Inventory::where('product_id', $productId)->first();
    }
}