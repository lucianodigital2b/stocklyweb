<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Models\OrderHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        
        // Apply search filter if provided
        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }
        
        // Apply status filter if provided
        if ($request->has('status')) {
            $filters['status'] = $request->input('status');
        }
        
        $perPage = $request->input('per_page', 10);
        $orders = $this->orderService->getOrders($filters, $perPage);
        
        return response()->json($orders);
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

    /**
     * Add order history entry.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addHistory(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'status' => 'nullable|string|max:50'
        ]);

        $history = OrderHistory::create([
            'order_id' => $id,
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'data' => $history->load('user'),
        ], 201);
    }

    /**
     * Get order history.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getHistory(int $id): JsonResponse
    {
        $history = OrderHistory::where('order_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $history,
        ]);
    }

    /**
     * Update order status and add history entry.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|max:50',
            'description' => 'nullable|string|max:255'
        ]);

        $order = $this->orderService->getOrder($id);
        $oldStatus = $order->status;
        
        // Update order status
        $order->update(['status' => $request->input('status')]);

        // Add history entry
        $description = $request->input('description') ?: "Status changed from {$oldStatus} to {$request->input('status')}";
        
        OrderHistory::create([
            'order_id' => $id,
            'description' => $description,
            'status' => $request->input('status'),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'data' => new OrderResource($order->fresh()),
        ]);
    }
}