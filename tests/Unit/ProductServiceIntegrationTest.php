<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Services\ProductService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ProductServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $productService;
    protected $company;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
        
        // Create a company with a warehouse
        $this->company = Company::factory()->create();
        $this->warehouse = Warehouse::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Depósito Principal',
            'status' => 'active'
        ]);
        
        // Set the current company context
        app()->instance('current_company', $this->company);
    }

    /**
     * Test that inventory is created when a product is created.
     */
    public function test_inventory_is_created_when_product_is_created()
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'description' => 'A test product',
            'stock' => 10,
            'status' => 'active',
            'type' => 'simple',
        ];

        // Create the product
        $product = $this->productService->createProduct($productData, []);

        // Assert the product was created
        $this->assertNotNull($product);
        $this->assertEquals('Test Product', $product->name);

        // Assert that inventory was created for the product
        $inventory = Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        $this->assertNotNull($inventory, 'Inventory should be created for the product');
        $this->assertEquals($product->id, $inventory->product_id);
        $this->assertEquals($this->warehouse->id, $inventory->warehouse_id);
        $this->assertEquals(0, $inventory->stock); // Default stock should be 0
        $this->assertFalse($inventory->is_infinite); // Default should be false
    }

    /**
     * Test that inventory is not duplicated if it already exists.
     */
    public function test_inventory_is_not_duplicated_if_already_exists()
    {
        // Create a product first
        $productData = [
            'name' => 'Test Product 2',
            'sku' => 'TEST-002',
            'price' => 149.99,
            'description' => 'Another test product',
            'stock' => 5,
            'status' => 'active',
            'type' => 'simple',
        ];

        $product = $this->productService->createProduct($productData, []);

        // Count inventories before attempting to create another
        $inventoryCountBefore = Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->count();

        // Try to create inventory again (this should not create a duplicate)
        $this->productService->createInventoryForProduct($product);

        // Count inventories after
        $inventoryCountAfter = Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->count();

        $this->assertEquals($inventoryCountBefore, $inventoryCountAfter, 'Inventory should not be duplicated');
        $this->assertEquals(1, $inventoryCountAfter, 'There should be exactly one inventory record');
    }
}