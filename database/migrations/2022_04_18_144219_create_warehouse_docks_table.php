<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseDocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_docks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('dock_id');
            $table->index('dock_id');
            $table->foreign('dock_id')->references('id')->on('docks')->onDelete('cascade');

            $table->unsignedBigInteger('warehouse_id');
            $table->index('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');

            $table->integer('processing_days')->default(0)->nullable();
            $table->integer('processing_seconds')->default(0)->nullable();
            $table->decimal('extra_fee')->default(0)->nullable();

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
        Schema::dropIfExists('warehouse_docks');
    }
}
