<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\CustomerMeta;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $customerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = new CustomerService();
    }

    /** @test */
    public function it_can_create_a_customer_with_meta()
    {
        // Create customer data
        $customerData = [
            'store_id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ];

        // Create meta data
        $metaData = [
            'preferred_language' => 'en',
            'newsletter_subscription' => 'true',
        ];

        // Create customer
        $customer = $this->customerService->createCustomer($customerData, $metaData);

        // Assert customer was created
        $this->assertDatabaseHas('customers', $customerData);

        // Assert meta data was created
        foreach ($metaData as $key => $value) {
            $this->assertDatabaseHas('customer_meta', [
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /** @test */
    public function it_can_update_a_customer_and_meta()
    {
        // Create a customer
        $customer = Customer::factory()->create();

        // Create initial meta data
        $initialMeta = [
            'preferred_language' => 'en',
            'newsletter_subscription' => 'true',
        ];
        foreach ($initialMeta as $key => $value) {
            CustomerMeta::create([
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }

        // Update customer data
        $updatedCustomerData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        // Update meta data
        $updatedMetaData = [
            'preferred_language' => 'es',
            'newsletter_subscription' => 'false',
        ];

        // Update customer
        $updatedCustomer = $this->customerService->updateCustomer($customer->id, $updatedCustomerData, $updatedMetaData);

        // Assert customer was updated
        $this->assertDatabaseHas('customers', $updatedCustomerData);

        // Assert meta data was updated
        foreach ($updatedMetaData as $key => $value) {
            $this->assertDatabaseHas('customer_meta', [
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /** @test */
    public function it_can_get_a_customer_with_meta()
    {
        // Create a customer
        $customer = Customer::factory()->create();

        // Create meta data
        $metaData = [
            'preferred_language' => 'en',
            'newsletter_subscription' => 'true',
        ];
        foreach ($metaData as $key => $value) {
            CustomerMeta::create([
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }

        // Get customer with meta
        $retrievedCustomer = $this->customerService->getCustomer($customer->id);

        // Assert customer data is correct
        $this->assertEquals($customer->id, $retrievedCustomer->id);
        $this->assertEquals($customer->name, $retrievedCustomer->name);

        // Assert meta data is correct
        foreach ($metaData as $key => $value) {
            $this->assertEquals($value, $retrievedCustomer->meta->where('key', $key)->first()->value);
        }
    }

    /** @test */
    public function it_can_delete_a_customer_and_meta()
    {
        // Create a customer
        $customer = Customer::factory()->create();

        // Create meta data
        $metaData = [
            'preferred_language' => 'en',
            'newsletter_subscription' => 'true',
        ];
        foreach ($metaData as $key => $value) {
            CustomerMeta::create([
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }

        // Delete customer
        $this->customerService->deleteCustomer($customer->id);

        // Assert customer was deleted
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);

        // Assert meta data was deleted
        foreach ($metaData as $key => $value) {
            $this->assertDatabaseMissing('customer_meta', [
                'customer_id' => $customer->id,
                'key' => $key,
            ]);
        }
    }

    /** @test */
    public function it_can_update_customer_meta()
    {
        // Create a customer
        $customer = Customer::factory()->create();

        // Create initial meta data
        $initialMeta = [
            'preferred_language' => 'en',
        ];
        foreach ($initialMeta as $key => $value) {
            CustomerMeta::create([
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }

        // Update meta data
        $updatedMeta = $this->customerService->updateCustomerMeta($customer->id, 'preferred_language', 'es');

        // Assert meta data was updated
        $this->assertDatabaseHas('customer_meta', [
            'customer_id' => $customer->id,
            'key' => 'preferred_language',
            'value' => 'es',
        ]);
    }

    /** @test */
    public function it_can_delete_customer_meta()
    {
        // Create a customer
        $customer = Customer::factory()->create();

        // Create meta data
        $metaData = [
            'preferred_language' => 'en',
        ];
        foreach ($metaData as $key => $value) {
            CustomerMeta::create([
                'customer_id' => $customer->id,
                'key' => $key,
                'value' => $value,
            ]);
        }

        // Delete meta data
        $this->customerService->deleteCustomerMeta($customer->id, 'preferred_language');

        // Assert meta data was deleted
        $this->assertDatabaseMissing('customer_meta', [
            'customer_id' => $customer->id,
            'key' => 'preferred_language',
        ]);
    }
}