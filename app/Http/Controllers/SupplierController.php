<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
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
            'limit' => $request->input('per_page', 10)
        ];
        
        if(!empty($request->input('q'))){
            $args['q'] = $request->input('q');
        }

        $suppliers = $this->supplierService->list($args);
        
        return response()->json($suppliers);
    }

    /**
     * Store a new supplier.
     *
     * @param SupplierRequest $request
     * @return JsonResponse
     */
    public function store(SupplierRequest $request): JsonResponse
    {
        $supplier = $this->supplierService->createSupplier($request->validated());

        return response()->json($supplier, 201);
    }

    /**
     * Get a supplier by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplier($id);

        return response()->json($supplier);
    }

    /**
     * Update an existing supplier.
     *
     * @param SupplierRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SupplierRequest $request, int $id): JsonResponse
    {
        $supplier = $this->supplierService->updateSupplier($id, $request->validated());

        return response()->json($supplier);
    }

    /**
     * Delete a supplier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->supplierService->deleteSupplier($id);

        return response()->json(null, 204);
    }
}
