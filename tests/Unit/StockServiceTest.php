<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockService;
use App\Exceptions\InsufficientStockException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    /**
     * Test deducting stock for a product without variants.
     */
    public function test_deduct_stock_for_product_without_variants()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Deduct stock
        $this->stockService->deductStock($product->id, null, 5);

        // Assert the stock was deducted
        $this->assertEquals(5, $inventory->fresh()->quantity);
    }

    /**
     * Test deducting stock for a product without variants when stock is insufficient.
     */
    public function test_deduct_stock_for_product_without_variants_insufficient_stock()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Expect an exception
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock for ID: ' . $product->id);

        // Deduct stock
        $this->stockService->deductStock($product->id, null, 5);
    }

    /**
     * Test restoring stock for a product without variants.
     */
    public function test_restore_stock_for_product_without_variants()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Restore stock
        $this->stockService->restoreStock($product->id, null, 5);

        // Assert the stock was restored
        $this->assertEquals(15, $inventory->fresh()->quantity);
    }

    /**
     * Test deducting stock for a product with variants.
     */
    public function test_deduct_stock_for_product_with_variants()
    {
        // Create a product variant and its inventory
        $variant = ProductVariant::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        // Deduct stock
        $this->stockService->deductStock(null, $variant->id, 5);

        // Assert the stock was deducted
        $this->assertEquals(5, $inventory->fresh()->quantity);
    }

    /**
     * Test deducting stock for a product with variants when stock is insufficient.
     */
    public function test_deduct_stock_for_product_with_variants_insufficient_stock()
    {
        // Create a product variant and its inventory
        $variant = ProductVariant::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        // Expect an exception
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock for ID: ' . $variant->id);

        // Deduct stock
        $this->stockService->deductStock(null, $variant->id, 5);
    }

    /**
     * Test restoring stock for a product with variants.
     */
    public function test_restore_stock_for_product_with_variants()
    {
        // Create a product variant and its inventory
        $variant = ProductVariant::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        // Restore stock
        $this->stockService->restoreStock(null, $variant->id, 5);

        // Assert the stock was restored
        $this->assertEquals(15, $inventory->fresh()->quantity);
    }
}