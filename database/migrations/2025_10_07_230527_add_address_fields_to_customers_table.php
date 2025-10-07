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
        Schema::table('customers', function (Blueprint $table) {
            // Document number field (CPF or CNPJ)
            $table->string('document_number', 18)->nullable()->after('phone');
            
            // Brazilian address fields
            $table->string('cep', 9)->nullable()->after('document_number');
            $table->string('address')->nullable()->after('cep');
            $table->string('number', 20)->nullable()->after('address');
            $table->string('neighborhood')->nullable()->after('number');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
            
            // Additional customer fields
            $table->date('birth_date')->nullable()->after('state');
            $table->enum('customer_type', ['regular', 'vip', 'corporate'])->default('regular')->after('birth_date');
            $table->boolean('newsletter_subscription')->default(false)->after('customer_type');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->after('newsletter_subscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'document_number',
                'cep',
                'address',
                'number',
                'neighborhood',
                'city',
                'state',
                'birth_date',
                'customer_type',
                'newsletter_subscription',
                'status'
            ]);
        });
    }
};
