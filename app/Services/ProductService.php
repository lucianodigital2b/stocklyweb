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
        $products = Product::with('store');

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
            // Create the product
            $product = Product::create($productData);

            // Add variants if provided
            if (!empty($variantsData)) {
                foreach ($variantsData as $variantData) {
                    $this->addVariant($product->id, $variantData);
                }
            }
            if (isset($productData['thumbnail'])) {
                $product->addMedia($productData['thumbnail'])->toMediaCollection('thumbnail');
            }
            
            if (isset($productData['gallery'])) {
                foreach ($productData['gallery'] as $image) {
                    $product->addMedia($image)->toMediaCollection('gallery');
                }
            }


            DB::commit();

            return $product;
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

            // Update product data
            $product->update($productData);

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

            return $product;
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

            // Delete associated variants
            $product->variants()->delete();

            // Delete the product
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