<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Exceptions\InsufficientStockException;

class StockService
{
    /**
     * Deduct stock for a product or product variant and record the movement.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @param int $quantity
     * @param string $movementType
     * @param int|null $referenceId
     * @throws InsufficientStockException
     */
    public function deductStock(?int $productId, ?int $variantId, int $quantity, string $movementType = 'order', ?int $referenceId = null): void
    {
        // Ensure either productId or variantId is provided
        if (!$productId && !$variantId) {
            throw new \InvalidArgumentException('Either productId or variantId must be provided.');
        }

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
        $this->recordStockMovement($productId, $variantId, -$quantity, $movementType, $referenceId);
    }

    /**
     * Restore stock for a product or product variant and record the movement.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @param int $quantity
     * @param string $movementType
     * @param int|null $referenceId
     */
    public function restoreStock(?int $productId, ?int $variantId, int $quantity, string $movementType = 'order_cancellation', ?int $referenceId = null): void
    {
        // Ensure either productId or variantId is provided
        if (!$productId && !$variantId) {
            throw new \InvalidArgumentException('Either productId or variantId must be provided.');
        }

        // Find the inventory record
        $inventory = $this->findInventory($productId, $variantId);

        if ($inventory) {
            // Restore the stock
            $inventory->quantity += $quantity;
            $inventory->save();

            // Record the stock movement
            $this->recordStockMovement($productId, $variantId, $quantity, $movementType, $referenceId);
        }
    }

    /**
     * Record a stock movement for a product or product variant.
     *
     * @param int|null $productId
     * @param int|null $variantId
     * @param int $quantityChange
     * @param string $movementType
     * @param int|null $referenceId
     */
    protected function recordStockMovement(?int $productId, ?int $variantId, int $quantityChange, string $movementType, ?int $referenceId = null): void
    {
        StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity_change' => $quantityChange,
            'movement_type' => $movementType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Find the inventory record for a product or product variant.
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
}