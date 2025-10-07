<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerMeta;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * Get a paginated list of customers with optional search and filtering.
     *
     * @param array $args
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function list(array $args = [])
    {
        $query = Customer::with(['store', 'orders']);

        // Apply search filter
        if (!empty($args['q'])) {
            $searchTerm = $args['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('document_number', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply store filter if provided
        if (!empty($args['store_id'])) {
            $query->where('store_id', $args['store_id']);
        }

        // Order by most recent first
        $query->orderBy('id', 'desc');

        // Apply pagination
        $limit = $args['limit'] ?? 10;
        return $query->paginate($limit);
    }

    /**
     * Create a new customer with optional meta data.
     *
     * @param array $customerData
     * @param array $metaData
     * @return Customer
     */
    public function createCustomer(array $customerData, array $metaData = []): Customer
    {
        return DB::transaction(function () use ($customerData, $metaData) {
            // Create the customer
            $customer = Customer::create($customerData);

            // Add meta data if provided
            if (!empty($metaData)) {
                foreach ($metaData as $key => $value) {
                    $customer->meta()->create([
                        'key' => $key,
                        'value' => $value,
                    ]);
                }
            }

            return $customer;
        });
    }

    /**
     * Update an existing customer and their meta data.
     *
     * @param int $customerId
     * @param array $customerData
     * @param array $metaData
     * @return Customer
     */
    public function updateCustomer(int $customerId, array $customerData, array $metaData = []): Customer
    {
        return DB::transaction(function () use ($customerId, $customerData, $metaData) {
            // Find the customer
            $customer = Customer::findOrFail($customerId);

            // Update customer details
            $customer->update($customerData);

            // Update or create meta data
            if (!empty($metaData)) {
                foreach ($metaData as $key => $value) {
                    $customer->meta()->updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }
            }

            return $customer->fresh();
        });
    }

    /**
     * Get a customer by ID with their meta data.
     *
     * @param int $customerId
     * @return Customer
     */
    public function getCustomer(int $customerId): Customer
    {
        return Customer::with('meta')->findOrFail($customerId);
    }

    /**
     * Delete a customer and their meta data.
     *
     * @param int $customerId
     * @return void
     */
    public function deleteCustomer(int $customerId): void
    {
        DB::transaction(function () use ($customerId) {
            $customer = Customer::findOrFail($customerId);
            $customer->meta()->delete();
            $customer->delete();
        });
    }

    /**
     * Add or update meta data for a customer.
     *
     * @param int $customerId
     * @param string $key
     * @param string $value
     * @return CustomerMeta
     */
    public function updateCustomerMeta(int $customerId, string $key, string $value): CustomerMeta
    {
        $customer = Customer::findOrFail($customerId);

        return $customer->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Remove a specific meta key for a customer.
     *
     * @param int $customerId
     * @param string $key
     * @return void
     */
    public function deleteCustomerMeta(int $customerId, string $key): void
    {
        $customer = Customer::findOrFail($customerId);
        $customer->meta()->where('key', $key)->delete();
    }
}