<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    /**
     * Get a paginated list of categories with optional search and filtering.
     *
     * @param array $args
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function list(array $args = [])
    {
        $query = Category::with(['store', 'products']);

        // Apply search filter
        if (!empty($args['q'])) {
            $searchTerm = $args['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply store filter if provided
        if (!empty($args['store_id'])) {
            $query->where('store_id', $args['store_id']);
        }

        // Apply company filter if provided
        if (!empty($args['company_id'])) {
            $query->where('company_id', $args['company_id']);
        }

        // Apply additional where conditions
        if (isset($args['where'])) {
            foreach ($args['where'] as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, '=', $value);
                }
            }
        }

        // Order by most recent first
        $query->orderBy('id', 'desc');

        // Apply pagination or return all
        if (isset($args['limit'])) {
            return $query->paginate($args['limit']);
        } else {
            return $query->get();
        }
    }

    /**
     * Create a new category.
     *
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        DB::beginTransaction();

        try {
            // Add company_id from authenticated user if not provided
            // if (!isset($data['company_id'])) {
            //     $data['company_id'] = auth()->user()->company_id;
            // }

            // Create the category
            // dd($data);
            $category = Category::create($data);

            DB::commit();

            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create category: ' . $e->getMessage());
            throw new \RuntimeException('Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Get a category by ID.
     *
     * @param int|string $id
     * @return Category
     */
    public function getCategory($id): Category
    {
        return Category::with(['store', 'products'])->findOrFail($id);
    }

    /**
     * Update an existing category.
     *
     * @param int|string $id
     * @param array $data
     * @return Category
     */
    public function updateCategory(int|string $id, array $data): Category
    {
        DB::beginTransaction();

        try {
            // Validate ID
            $categoryId = (int) $id;
            if ($categoryId <= 0) {
                throw new \InvalidArgumentException('Invalid category ID provided');
            }

            // Find the category
            $category = Category::findOrFail($categoryId);

            // Update category data
            $category->update($data);

            DB::commit();

            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update category: ' . $e->getMessage());
            throw new \RuntimeException('Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Delete a category.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteCategory(int|string $id): bool
    {
        DB::beginTransaction();

        try {
            // Validate ID
            $categoryId = (int) $id;
            if ($categoryId <= 0) {
                throw new \InvalidArgumentException('Invalid category ID provided');
            }

            // Find the category
            $category = Category::findOrFail($categoryId);

            // Check if category has products
            if ($category->products()->count() > 0) {
                throw new \RuntimeException('Cannot delete category that has products assigned to it.');
            }

            // Delete the category
            $category->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete category: ' . $e->getMessage());
            throw new \RuntimeException('Failed to delete category: ' . $e->getMessage());
        }
    }

    /**
     * Get categories for tree structure (used in dropdowns).
     *
     * @param array $args
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesTree(array $args = [])
    {
        $query = Category::query();

        // Apply company filter
        if (!empty($args['company_id'])) {
            $query->where('company_id', $args['company_id']);
        } else if (auth()->check()) {
            $query->where('company_id', auth()->user()->company_id);
        }

        // Apply store filter if provided
        if (!empty($args['store_id'])) {
            $query->where('store_id', $args['store_id']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats(int|string $id): array
    {
        // Validate and cast ID to integer
        $categoryId = (int) $id;
        if ($categoryId <= 0) {
            throw new \InvalidArgumentException('Category ID must be a positive integer');
        }

        // Verify category exists
        $category = Category::findOrFail($categoryId);

        $totalProducts = $category->products()->count();
        $activeProducts = $category->products()->where('status', 'active')->count();
        $totalStock = $category->products()->sum('stock_quantity');

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_stock' => $totalStock,
        ];
    }
}