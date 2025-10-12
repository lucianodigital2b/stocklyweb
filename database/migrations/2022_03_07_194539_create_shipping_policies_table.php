<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_policies', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->string('status')->default('ativa');
            $table->unsignedBigInteger('shipping_method_id')->nullable();
            $table->index('shipping_method_id');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
            $table->integer('sum_dimensions')->default('0');
            $table->integer('biggest_edge')->default('0');
            $table->integer('cubic_weight_factor')->default('0');
            $table->integer('minimum_weight_factor')->default('0');
            $table->integer('delivery_saturdays')->default('0');
            $table->integer('delivery_sundays')->default('0');
            $table->integer('delivery_holidays')->default('0'); 
            $table->integer('minimum_items');
            $table->integer('minimum_value');
            $table->integer('maximum_value');
            $table->integer('relate_pick_up_points')->default('0');
            $table->integer('modal_undefined')->default('0');
            $table->integer('modal_chemicals')->default('0');
            $table->integer('modal_liquids')->default('0');
            $table->integer('modal_white_line')->default('0');
            $table->integer('modal_electronics')->default('0');
            $table->integer('modal_mattresses')->default('0');
            $table->integer('modal_fire_gun')->default('0');
            $table->integer('modal_furniture')->default('0');
            $table->integer('modal_refrigerators')->default('0');
            $table->integer('modal_glass')->default('0');
            $table->integer('modal_tires')->default('0');
            $table->string('time_type')->default('shipping');
            $table->string('monday_hour')->nullable();
            $table->string('tuesday_hour')->nullable();
            $table->string('wednesday_hour')->nullable();
            $table->string('thursday_hour')->nullable();
            $table->string('friday_hour')->nullable();
            $table->string('saturday_hour')->nullable();
            $table->string('sunday_hour')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_policies');
    }
}
