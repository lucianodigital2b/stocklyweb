<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Store a new order.
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated(), $request->input('meta', []));
        return response()->json([
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * Get an order by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);
        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update an existing order.
     *
     * @param UpdateOrderRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrder($id, $request->validated(), $request->input('meta', []));
        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Delete an order.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->orderService->deleteOrder($id);
        return response()->json(null, 204);
    }

    /**
     * Update order meta.
     *
     * @param int $id
     * @param string $key
     * @return JsonResponse
     */
    public function updateMeta(int $id, string $key): JsonResponse
    {
        $request = request();
        $meta = $this->orderService->updateOrderMeta($id, $key, $request->input('value'));
        return response()->json([
            'data' => $meta,
        ]);
    }

    /**
     * Delete order meta.
     *
     * @param int $id
     * @param string $key
     * @return JsonResponse
     */
    public function deleteMeta(int $id, string $key): JsonResponse
    {
        $this->orderService->deleteOrderMeta($id, $key);
        return response()->json(null, 204);
    }
}