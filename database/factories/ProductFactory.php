<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'store_id' => function () {
                if ($store = Store::inRandomOrder()->first()) {
                    return $store->id;
                }

                return factory(Store::class)->create()->id;
            },
            'name' => $this->faker->words(3, true), // Generate a fake product name
            'description' => $this->faker->paragraph, // Generate a fake description
            'price' => $this->faker->randomFloat(2, 10, 1000), // Generate a fake price between 10 and 1000
            'sku' => $this->faker->unique()->bothify('SKU-#####'), // Generate a unique SKU
        ];
    }
}