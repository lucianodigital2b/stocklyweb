<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model
{
    use HasFactory, InteractsWithMedia;
    
    protected $fillable = [
        'name',
        'sku',
        'price',
        'description',
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
        'barcode',
        'quantityOnShelf',
        'quantityInWarehouse',
        'allowBackorders',
    ];

    const PRODUCT_TYPES = [
        'simple',
        'variable',
        'variation',
    ];

    const PRODUCT_TYPES_LABELS = [
        'simple' => 'Simples',
        'variable' => 'Variável',
        'variation' => 'Variação'
    ];

    // Shopify Product Status Constants
    const PRODUCT_STATUS_ACTIVE = 'active';
    const PRODUCT_STATUS_ARCHIVED = 'archived';
    const PRODUCT_STATUS_DRAFT = 'draft';

    const PRODUCT_STATUSES = [
        self::PRODUCT_STATUS_ACTIVE,
        self::PRODUCT_STATUS_ARCHIVED,
        self::PRODUCT_STATUS_DRAFT,
    ];

    const PRODUCT_STATUS_LABELS = [
        self::PRODUCT_STATUS_ACTIVE => 'Ativo',
        self::PRODUCT_STATUS_ARCHIVED => 'Arquivado',
        self::PRODUCT_STATUS_DRAFT => 'Rascunho',
    ];

    /**
     * Produto simples
     */
    const PRODUCT_TYPE_SIMPLE = 'simple';

    /**
     * Produto com variações
     */
    const PRODUCT_TYPE_VARIABLE = 'variable';

    /**
     * Variação de produto
     */
    const PRODUCT_TYPE_VARIATION = 'variation';

    const THUMB_FOLDER = 'product';
    const MEDIAS_FOLDER = 'product-medias';


    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function metas()
    {
        return $this->hasMany(ProductMeta::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}