<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inventory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => function () {
                if ($warehouse = Warehouse::inRandomOrder()->first()) {
                    return $warehouse->id;
                }
                return Warehouse::factory()->create()->id;
            },
            'product_id' => function () {
                if ($product = Product::inRandomOrder()->first()) {
                    return $product->id;
                }
                return Product::factory()->create()->id;
            },
            'stock' => $this->faker->numberBetween(0, 100),
            'is_infinite' => false,
        ];
    }
}
