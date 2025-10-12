<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocksShippingPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docks_shipping_policies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('dock_id');
            $table->index('dock_id');
            $table->foreign('dock_id')->references('id')->on('docks')->onDelete('cascade');
            
            $table->unsignedBigInteger('shipping_policy_id');
            $table->index('shipping_policy_id');
            $table->foreign('shipping_policy_id')->references('id')->on('shipping_policies')->onDelete('cascade');

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
        Schema::dropIfExists('docks_shipping_policies');
    }
}
