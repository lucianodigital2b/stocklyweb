<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderService;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderCreationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    /**
     * Test creating an order for a product without variants.
     */
    public function test_create_order_for_product_without_variants()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Create order data
        $orderData = [
            'store_id' => 1,
            'customer_id' => 1,
            'status' => 'pending',
            'total_price' => 100.00,
        ];

        $orderItems = [
            [
                'product_id' => $product->id,
                'quantity' => 5,
                'price' => 50.00,
            ],
        ];

        // Create the order
        $order = $this->orderService->createOrder($orderData, $orderItems);

        // Assert the order was created
        $this->assertNotNull($order);
        $this->assertEquals(1, $order->items->count());

        // Assert the stock was deducted
        $this->assertEquals(5, $inventory->fresh()->quantity);
    }

    /**
     * Test creating an order for a product without variants when stock is insufficient.
     */
    public function test_create_order_for_product_without_variants_insufficient_stock()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Create order data
        $orderData = [
            'store_id' => 1,
            'customer_id' => 1,
            'status' => 'pending',
            'total_price' => 100.00,
        ];

        $orderItems = [
            [
                'product_id' => $product->id,
                'quantity' => 5,
                'price' => 50.00,
            ],
        ];

        // Expect an exception
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock for ID: ' . $product->id);

        // Create the order
        $this->orderService->createOrder($orderData, $orderItems);
    }

    /**
     * Test creating an order for a product with variants.
     */
    public function test_create_order_for_product_with_variants()
    {
        // Create a product variant and its inventory
        $variant = ProductVariant::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        // Create order data
        $orderData = [
            'store_id' => 1,
            'customer_id' => 1,
            'status' => 'pending',
            'total_price' => 100.00,
        ];

        $orderItems = [
            [
                'product_variant_id' => $variant->id,
                'quantity' => 5,
                'price' => 50.00,
            ],
        ];

        // Create the order
        $order = $this->orderService->createOrder($orderData, $orderItems);

        // Assert the order was created
        $this->assertNotNull($order);
        $this->assertEquals(1, $order->items->count());

        // Assert the stock was deducted
        $this->assertEquals(5, $inventory->fresh()->quantity);
    }

    /**
     * Test creating an order for a product with variants when stock is insufficient.
     */
    public function test_create_order_for_product_with_variants_insufficient_stock()
    {
        // Create a product variant and its inventory
        $variant = ProductVariant::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        // Create order data
        $orderData = [
            'store_id' => 1,
            'customer_id' => 1,
            'status' => 'pending',
            'total_price' => 100.00,
        ];

        $orderItems = [
            [
                'product_variant_id' => $variant->id,
                'quantity' => 5,
                'price' => 50.00,
            ],
        ];

        // Expect an exception
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock for ID: ' . $variant->id);

        // Create the order
        $this->orderService->createOrder($orderData, $orderItems);
    }

    /**
     * Test updating an order.
     */
    public function test_update_order()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Create an order
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        // Update order data
        $orderData = [
            'status' => 'completed',
        ];

        $orderItems = [
            [
                'id' => $orderItem->id,
                'quantity' => 3,
                'price' => 60.00,
            ],
        ];

        // Update the order
        $updatedOrder = $this->orderService->updateOrder($order->id, $orderData, $orderItems);

        // Assert the order was updated
        $this->assertEquals('completed', $updatedOrder->status);
        $this->assertEquals(3, $updatedOrder->items->first()->quantity);

        // Assert the stock was updated
        $this->assertEquals(7, $inventory->fresh()->quantity);
    }

    /**
     * Test deleting an order.
     */
    public function test_delete_order()
    {
        // Create a product and its inventory
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Create an order
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        // Delete the order
        $this->orderService->deleteOrder($order->id);

        // Assert the order was deleted
        $this->assertNull(Order::find($order->id));

        // Assert the stock was restored
        $this->assertEquals(12, $inventory->fresh()->quantity);
    }

    /**
     * Test getting an order by ID.
     */
    public function test_get_order()
    {
        // Create an order
        $order = Order::factory()->create();

        // Get the order
        $foundOrder = $this->orderService->getOrder($order->id);

        // Assert the order was found
        $this->assertEquals($order->id, $foundOrder->id);
    }

    /**
     * Test getting a paginated list of orders.
     */
    public function test_get_orders()
    {
        // Create orders
        Order::factory()->count(15)->create();

        // Get paginated orders
        $orders = $this->orderService->getOrders([], 10);

        // Assert the correct number of orders were returned
        $this->assertEquals(15, $orders->total());
        $this->assertEquals(10, $orders->count());
    }
}