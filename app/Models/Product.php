<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, MultiTenant;
    
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
        'allow_backorders',
        'store_id'
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

    const MEDIAS_FOLDER = 'product-medias';

    /**
     * Register media collections for the product.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Register media conversions for the product.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('thumbnail', 'gallery');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('thumbnail', 'gallery');
    }

    /**
     * Get the product's thumbnail image URL.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $thumbnail = $this->getFirstMedia('thumbnail');
        return $thumbnail ? $thumbnail->getUrl() : null;
    }

    /**
     * Get the product's thumbnail image URL with conversion.
     */
    public function getThumbnailThumbUrlAttribute(): ?string
    {
        $thumbnail = $this->getFirstMedia('thumbnail');
        return $thumbnail ? $thumbnail->getUrl('thumb') : null;
    }

    /**
     * Get all gallery images URLs.
     */
    public function getGalleryUrlsAttribute(): array
    {
        return $this->getMedia('gallery')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
    }

    /**
     * Get all gallery images URLs with conversion.
     */
    public function getGalleryThumbUrlsAttribute(): array
    {
        return $this->getMedia('gallery')->map(function ($media) {
            return $media->getUrl('thumb');
        })->toArray();
    }


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