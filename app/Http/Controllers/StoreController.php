<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Create a new store.
     *
     * @param StoreStoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreStoreRequest $request): JsonResponse
    {
        $user = $request->user(); 
        $store = $this->storeService->createStore($user, $request->validated(), $request->input('onboarding', []));

        return response()->json([
            'data' => new StoreResource($store),
        ], 201);
    }

    /**
     * Get a store by ID.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function show(Store $store): JsonResponse
    {
        return response()->json([
            'data' => new StoreResource($store),
        ]);
    }

    /**
     * Create a subscription for the store.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function createSubscription(Store $store): JsonResponse
    {
        $request = request();
        $subscription = $this->storeService->createSubscription($store, $request->input('plan_id'));

        return response()->json([
            'data' => $subscription,
        ]);
    }

    /**
     * Change the store's subscription plan.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function changeSubscriptionPlan(Store $store): JsonResponse
    {
        $request = request();
        $this->storeService->changeSubscriptionPlan($store, $request->input('new_plan_id'));

        return response()->json([
            'message' => 'Subscription plan updated successfully.',
        ]);
    }

    /**
     * Cancel the store's subscription.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function cancelSubscription(Store $store): JsonResponse
    {
        $this->storeService->cancelSubscription($store);

        return response()->json([
            'message' => 'Subscription canceled successfully.',
        ]);
    }

    /**
     * Update the store's payment method.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function updatePaymentMethod(Store $store): JsonResponse
    {
        $request = request();
        $this->storeService->updatePaymentMethod($store, $request->input('payment_method_id'));

        return response()->json([
            'message' => 'Payment method updated successfully.',
        ]);
    }

    /**
     * Get the Stripe customer portal URL for the store.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function getCustomerPortalUrl(Store $store): JsonResponse
    {
        $portalUrl = $this->storeService->getCustomerPortalUrl($store);

        return response()->json([
            'url' => $portalUrl,
        ]);
    }
}