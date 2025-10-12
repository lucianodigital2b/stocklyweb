<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true) . ' Warehouse',
            'status' => 'active',
            'company_id' => function () {
                if ($company = Company::inRandomOrder()->first()) {
                    return $company->id;
                }
                return Company::factory()->create()->id;
            },
        ];
    }
}