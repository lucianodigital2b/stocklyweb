<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }


    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $args = [
            'parent_id' => null, 
            'with_children' => true,
            'limit' => $request->input('per_page', 10)
        ];
        
        if(!empty($request->input('q'))){
            $args['q'] = $request->input('q');
        }

        $args['category_id'] = $request->input('category', false);

        $products = $this->productService->list($args);
        
        // Transform products for API response
        $transformedProducts = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price ?? 0,
                'category' => $product->categories->first()->name ?? 'Uncategorized',
                'rating' => rand(3, 5), // Mock rating for now
                'inventoryStatus' => $this->getInventoryStatus($product),
                'image' => 'bamboo-watch.jpg' // Mock image for now
            ];
        });
        

        dd($transformedProducts);
        return response()->json([
            'data' => $transformedProducts,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem()
        ]);
    }
    
    /**
     * Get inventory status based on product data
     */
    private function getInventoryStatus($product)
    {
        // Mock inventory status logic - you can implement actual logic based on your inventory system
        $statuses = ['INSTOCK', 'LOWSTOCK', 'OUTOFSTOCK'];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Store a new product.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        // Create the product with meta data
        $product = $this->productService->createProduct(
            $request->validated(),
            $request->input('meta', [])
        );

        // Return the product resource
        return response()->json([
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Get a product by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update an existing product.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        // Update the product with meta data
        $product = $this->productService->updateProduct(
            $id,
            $request->validated(),
            $request->input('meta', [])
        );

        // Return the updated product resource
        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Delete the product
        $this->productService->deleteProduct($id);

        // Return a 204 No Content response
        return response()->json(null, 204);
    }

    /**
     * Update product meta.
     *
     * @param int $id
     * @param string $key
     * @return JsonResponse
     */
    public function updateMeta(int $id, string $key): JsonResponse
    {
        // Update the product meta
        $meta = $this->productService->updateProductMeta(
            $id,
            $key,
            request()->input('value')
        );

        // Return the updated meta
        return response()->json([
            'data' => $meta,
        ]);
    }

    /**
     * Delete product meta.
     *
     * @param int $id
     * @param string $key
     * @return JsonResponse
     */
    public function deleteMeta(int $id, string $key): JsonResponse
    {
        // Delete the product meta
        $this->productService->deleteProductMeta($id, $key);

        // Return a 204 No Content response
        return response()->json(null, 204);
    }
}