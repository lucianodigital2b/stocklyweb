<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;
use Laravel\Cashier\Subscription;
use Stripe\StripeClient;

class CheckStorePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stores:check-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if stores are late on payments and deactivate them if necessary.';

    protected $stripe;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all stores with active subscriptions
        $stores = Store::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })->get();

        foreach ($stores as $store) {
            // Get the Stripe subscription
            $subscription = $store->subscriptions()->first();

            if ($subscription) {
                // Retrieve the Stripe subscription details
                $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->stripe_id);

                // Check if the subscription is past due
                if ($stripeSubscription->status === 'past_due') {
                    // Deactivate the store
                    $store->update(['status' => 'inactive']);

                    // Cancel the subscription in Stripe
                    $subscription->cancel();

                    $this->info("Store {$store->name} (ID: {$store->id}) has been deactivated due to late payment.");
                }
            }
        }

        $this->info('Payment check completed.');
        return 0;
    }
}