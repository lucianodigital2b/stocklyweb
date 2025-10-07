<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\{Store, User};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreUser>
 */
class StoreUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => function () {
                if ($store = Store::inRandomOrder()->first()) {
                    return $store->id;
                }

                return factory(Store::class)->create()->id;
            },
            'user_id' => function () {
                if ($user = User::inRandomOrder()->first()) {
                    return $user->id;
                }

                return factory(User::class)->create()->id;
            },
        ];
    }
}
