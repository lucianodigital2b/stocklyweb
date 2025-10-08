<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->decimal('value', 18, 2);
            $table->foreignId('company_id');
            $table->foreignId('order_id')->nullable();
            $table->foreignId('cost_center_id')->nullable();
            $table->foreignId('supplier_id')->nullable();
            $table->date('due_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->tinyInteger('operation');
            $table->string('payment_method')->nullable();
            $table->longText('observations')->nullable();
            $table->string('external_code')->nullable();
            $table->longText('payment_info')->nullable();
            $table->string('account')->nullable();
            $table->string('bank_slip_url')->nullable()->after('charged_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
