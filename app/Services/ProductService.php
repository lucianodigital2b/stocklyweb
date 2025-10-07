<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
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
     * @param int $productId
     * @return Product
     */
    public function getProduct(int $productId): Product
    {
        return Product::with(['metas', 'variants', 'inventory', 'categories'])->findOrFail($id);
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

    /**
     * Add a variant to a product.
     *
     * @param int $productId
     * @param array $variantData
     * @return ProductVariant
     */
    public function addVariant(int $productId, array $variantData): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $productId,
            'name' => $variantData['name'],
            'price' => $variantData['price'],
            'sku' => $variantData['sku'],
        ]);
    }

    /**
     * Update a product variant.
     *
     * @param int $variantId
     * @param array $variantData
     * @return ProductVariant
     */
    public function updateVariant(int $variantId, array $variantData): ProductVariant
    {
        $variant = ProductVariant::findOrFail($variantId);
        $variant->update($variantData);

        return $variant;
    }

    /**
     * Delete a product variant.
     *
     * @param int $variantId
     * @return bool
     */
    public function deleteVariant(int $variantId): bool
    {
        $variant = ProductVariant::findOrFail($variantId);
        $variant->delete();

        return true;
    }
}