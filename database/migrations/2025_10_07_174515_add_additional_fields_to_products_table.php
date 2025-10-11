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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('sku');
            $table->decimal('promotional_price', 10, 2)->nullable()->after('price');
            $table->text('description_seo')->nullable()->after('description');
            $table->string('external_code')->nullable()->after('description_seo');
            $table->enum('type', ['simple', 'variable', 'variation'])->default('simple')->after('external_code');
            $table->decimal('weight', 15, 2)->nullable()->after('type');
            $table->decimal('width', 15, 2)->nullable()->after('weight');
            $table->decimal('lenght', 15, 2)->nullable()->after('width');
            $table->decimal('height', 15, 2)->nullable()->after('lenght');
            $table->enum('status', ['active', 'draft', 'archived'])->default('active')->after('height');
            $table->unsignedBigInteger('product_visibility_id')->nullable()->after('status');
            $table->timestamp('published_at')->nullable()->after('product_visibility_id');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade')->after('published_at');
            $table->foreignId('pair_id')->nullable()->constrained('products')->onDelete('set null')->after('product_id');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('ean')->nullable()->after('pair_id');
            $table->boolean('allow_backorders')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['pair_id']);
            $table->dropColumn([
                'stock',
                'promotional_price',
                'description_seo',
                'external_code',
                'type',
                'weight',
                'width',
                'lenght',
                'height',
                'status',
                'product_visibility_id',
                'published_at',
                'product_id',
                'pair_id',
                'ean',
                'allow_backorders'
            ]);
        });
    }
};
