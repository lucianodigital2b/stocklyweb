<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Store a new customer.
     *
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->createCustomer($request->validated(), $request->input('meta', []));
        return response()->json([
            'data' => new CustomerResource($customer),
        ], 201);
    }

    /**
     * Get a customer by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $customer = $this->customerService->getCustomer($id);
        return response()->json([
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Update an existing customer.
     *
     * @param UpdateCustomerRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        $customer = $this->customerService->updateCustomer($id, $request->validated(), $request->input('meta', []));
        return response()->json([
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Delete a customer.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->customerService->deleteCustomer($id);
        return response()->json(null, 204);
    }

    /**
     * Update customer meta.
     *
     * @param int $id
     * @param string $key
     * @return JsonResponse
     */
    public function updateMeta(int $id, string $key): JsonResponse
    {
        $request = request();
        $meta = $this->customerService->updateCustomerMeta($id, $key, $request->input('value'));
        return response()->json([
            'data' => $meta,
        ]);
    }

    /**
     * Delete customer meta.
     *
     * @param int $id
     * @param string $key
     * @return JsonResponse
     */
    public function deleteMeta(int $id, string $key): JsonResponse
    {
        $this->customerService->deleteCustomerMeta($id, $key);
        return response()->json(null, 204);
    }
}