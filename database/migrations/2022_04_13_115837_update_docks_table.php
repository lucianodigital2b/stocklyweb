<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('docks', function (Blueprint $table) {
            $table->string('id_erp')->unique()->after('id')->nullable();
            $table->string('name')->after('id_erp');

            $table->integer('service_time_days')->default(0)->nullable()->after('name');
            $table->integer('service_time_seconds')->default(0)->nullable()->after('service_time_days');
            $table->integer('overhead_days')->default(0)->nullable()->after('service_time_seconds');
            $table->integer('overhead_seconds')->default(0)->nullable()->after('overhead_days');


            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
