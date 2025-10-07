<?php

namespace Database\Seeders;

use App\Models\{Store, StoreUser, Customer, Product, User};   
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Store::factory(5)->create();
        User::factory(100)
        ->has(StoreUser::factory(1))
        ->create();
        Customer::factory(100)->create();
        Product::factory(1000)->create();

    }
}
