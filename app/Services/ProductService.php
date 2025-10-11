<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{


    public function list($data = []) 
    {
        $products = Product::query();

        if (isset($data['q'])) {
            if(isset($data['q']) && !empty($data['q'])) {   
                $products->where(function($query) use($data) {
                    $query->where('name', 'LIKE', '%'. $data['q'] . '%');
                    $query->orWhere('sku', '=', $data['q']);
                });
            }
        }

        // if(array_key_exists('parent_id', $data)) {
        //     if(is_null($data['parent_id'])) {
        //         $products->whereNull('product_id');
        //     }
        //     else if(is_array($data['parent_id'])) {
        //         $products->whereIn('product_id', $data['parent_id']);
        //     } else {
        //         $products->where('product_id', $data['parent_id']);
        //     }
        // }

        // if(isset($data['category_id']) && !empty($data['category_id'])) {
        //     $products = $products->whereHas('categories', function($q) use($data) {
        //         $q->where('categories.id', $data['category_id']);
        //     });
        // }

        // if(isset($data['with_children']) && !empty($data['with_children'])) {
        //     $products->with('children');
        // }

        if(isset($data['where'])) {
            foreach ($data['where'] as $field => $value) {
                if (is_array($value)) {
                    $products = $products->whereIn($field, $value);
                } else {
                    $products = $products->where($field, '=', $value);
                }
            }
        }

        $products = $products->orderBy('id', 'desc');

        if(isset($data['limit']) && !empty($data['limit'])) {
            $products = $products->paginate($data['limit']);
        } else {
            $products = $products->get();
        }
        
        return $products;
    }

    /**
     * Create a new product.
     *
     * @param array $productData
     * @param array $variantsData
     * @return Product
     */
    public function createProduct(array $productData, array $variantsData = []): Product
    {
        DB::beginTransaction();
        
        try {
            // Remove media data from product data to avoid database errors
            $gallery = $productData['gallery'] ?? [];
            $existingGallery = $productData['existing_gallery'] ?? [];
            unset($productData['gallery'], $productData['existing_gallery']);

            // Create the product
            $product = Product::create($productData);

            // Add variants if provided
            if (!empty($variantsData)) {
                foreach ($variantsData as $variantData) {
                    $this->addVariant($product->id, $variantData);
                }
            }

            // Handle gallery uploads - first image becomes thumbnail, rest go to gallery
            $allImages = array_merge($existingGallery, $gallery);
            
            if (!empty($allImages)) {
                foreach ($allImages as $index => $image) {
                    $collection = ($index === 0) ? 'thumbnail' : 'gallery';
                    
                    if (is_string($image)) {
                        // If it's a file path or URL
                        $product->addMediaFromUrl($image)->toMediaCollection($collection);
                    } else {
                        // If it's an uploaded file
                        $product->addMedia($image)->toMediaCollection($collection);
                    }
                }
            }

            DB::commit();

            return $product->fresh(['media']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create product: ' . $e->getMessage());
            throw new \RuntimeException('Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing product.
     *
     * @param int $productId
     * @param array $productData
     * @param array $variantsData
     * @return Product
     */
    public function updateProduct(int $productId, array $productData, array $variantsData = []): Product
    {
        DB::beginTransaction();

        try {
            // Find the product
            $product = Product::findOrFail($productId);

            // Remove media data from product data to avoid database errors
            $gallery = $productData['gallery'] ?? [];
            $existingGallery = $productData['existing_gallery'] ?? [];
            $replaceGallery = $productData['replace_gallery'] ?? true; // Default to replace for new gallery system
            unset($productData['gallery'], $productData['existing_gallery'], $productData['replace_gallery']);

            // Update product data
            $product->update($productData);

            // Handle gallery update - first image becomes thumbnail, rest go to gallery
            $allImages = array_merge($existingGallery, $gallery);
            
            if (!empty($allImages) || $replaceGallery) {
                if ($replaceGallery) {
                    // Clear existing media collections
                    $product->clearMediaCollection('thumbnail');
                    $product->clearMediaCollection('gallery');
                }

                if (!empty($allImages)) {
                    foreach ($allImages as $index => $image) {
                        $collection = ($index === 0) ? 'thumbnail' : 'gallery';
                        
                        if (is_string($image)) {
                            // If it's a file path or URL
                            $product->addMediaFromUrl($image)->toMediaCollection($collection);
                        } else {
                            // If it's an uploaded file
                            $product->addMedia($image)->toMediaCollection($collection);
                        }
                    }
                }
            }

            // Update or create variants
            foreach ($variantsData as $variantData) {
                if (isset($variantData['id'])) {
                    // Update existing variant
                    $this->updateVariant($variantData['id'], $variantData);
                } else {
                    // Create new variant
                    $this->addVariant($product->id, $variantData);
                }
            }

            DB::commit();

            return $product->fresh(['media']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product: ' . $e->getMessage());
            throw new \RuntimeException('Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Delete a product and its variants.
     *
     * @param int $productId
     * @return bool
     */
    public function deleteProduct(int $productId): bool
    {
        DB::beginTransaction();

        try {
            // Find the product
            $product = Product::findOrFail($productId);

            // Clear all media collections before deleting the product
            $product->clearMediaCollection('thumbnail');
            $product->clearMediaCollection('gallery');

            // Delete the product (this will also cascade delete related records)
            $product->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product: ' . $e->getMessage());
            throw new \RuntimeException('Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Get a product by ID with its variants.
     *
     * @param int $id
     * @return Product
     */
    public function getProduct(int $id): Product
    {
        return Product::with(['categories'])->findOrFail($id);
    }

    /**
     * Get all products with their variants.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getProducts(array $filters = [], int $perPage = 10)
    {
        $query = Product::with(['metas', 'variants', 'inventory', 'categories']);

        // Apply filters
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query->paginate($perPage);
    }
}