<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;

class StoreService
{
    /**
     * Create a new store and onboard the user.
     *
     * @param User $user
     * @param array $storeData
     * @param array $onboardingData
     * @return Store
     * @throws \Exception
     */
    public function createStore(User $user, array $storeData, array $onboardingData): Store
    {
        return DB::transaction(function () use ($user, $storeData, $onboardingData) {
            try {
                // Create the store
                $store = Store::create([
                    'name' => $storeData['name'],
                    'domain' => $storeData['domain'],
                    'currency' => $storeData['currency'] ?? 'USD',
                    'status' => 'pending', // Initial status
                ]);

                // Create a Stripe customer for the store using Cashier
                $stripeCustomer = $user->createAsStripeCustomer([
                    'name' => $store->name,
                    'metadata' => [
                        'store_id' => $store->id,
                    ],
                ]);

                // Save the Stripe customer ID to the store
                $store->stripe_id = $stripeCustomer->id;
                $store->save();

                // Handle onboarding steps (e.g., payment method setup)
                $this->handleOnboarding($store, $onboardingData);

                return $store;
            } catch (\Exception $e) {
                Log::error('Store creation failed: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Handle the onboarding process for the store.
     *
     * @param Store $store
     * @param array $onboardingData
     * @return void
     */
    protected function handleOnboarding(Store $store, array $onboardingData): void
    {
        // Example: Set up a payment method in Stripe
        if (isset($onboardingData['payment_method_id'])) {
            $store->updateDefaultPaymentMethod($onboardingData['payment_method_id']);
        }

        // Update store status to "active" after onboarding
        $store->update(['status' => 'active']);
    }

    /**
     * Create a Stripe subscription for the store.
     *
     * @param Store $store
     * @param string $planId
     * @return \Stripe\Subscription
     */
    public function createSubscription(Store $store, string $planId)
    {
        return $store->newSubscription('default', $planId)->create();
    }

    /**
     * Upgrade or downgrade the store's subscription plan.
     *
     * @param Store $store
     * @param string $newPlanId
     * @return void
     */
    public function changeSubscriptionPlan(Store $store, string $newPlanId): void
    {
        $store->subscription('default')->swap($newPlanId);
    }

    /**
     * Cancel the Stripe subscription for the store.
     *
     * @param Store $store
     * @return void
     */
    public function cancelSubscription(Store $store): void
    {
        $store->subscription('default')->cancel();
    }

    /**
     * Update the store's payment method.
     *
     * @param Store $store
     * @param string $paymentMethodId
     * @return void
     */
    public function updatePaymentMethod(Store $store, string $paymentMethodId): void
    {
        $store->updateDefaultPaymentMethod($paymentMethodId);
    }

    /**
     * Get the Stripe customer portal URL for the store.
     *
     * @param Store $store
     * @return string
     */
    public function getCustomerPortalUrl(Store $store): string
    {
        return $store->billingPortalUrl(route('dashboard'));
    }
}