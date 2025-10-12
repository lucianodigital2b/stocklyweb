<?php

namespace Tests\Unit;

use App\Http\Controllers\CompanyController;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Store;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class CompanyControllerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $companyController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyController = new CompanyController();
    }

    /**
     * Test that a default warehouse is created when a company is created.
     */
    public function test_default_warehouse_is_created_when_company_is_created()
    {
        $companyData = [
            'name' => 'Test Company',
            'document_number' => '12345678901',
            'email' => 'test@company.com',
            'phone' => '1234567890',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'status' => 'active',
        ];

        // Create a request with the company data
        $request = new Request($companyData);

        // Create the company using the controller
        $response = $this->companyController->store($request);

        // Get the created company
        $company = Company::where('name', 'Test Company')->first();
        $this->assertNotNull($company, 'Company should be created');

        // Assert that a default warehouse was created
        $warehouse = Warehouse::where('company_id', $company->id)
            ->where('name', 'Depósito Principal')
            ->first();

        $this->assertNotNull($warehouse, 'Default warehouse should be created');
        $this->assertEquals('Depósito Principal', $warehouse->name);
        $this->assertEquals('active', $warehouse->status);
        $this->assertEquals($company->id, $warehouse->company_id);

        // Assert that a default store was also created (existing functionality)
        $store = Store::where('company_id', $company->id)
            ->where('name', 'Loja Principal')
            ->first();

        $this->assertNotNull($store, 'Default store should be created');
        $this->assertEquals('Loja Principal', $store->name);
    }

    /**
     * Test that multiple companies each get their own default warehouse.
     */
    public function test_multiple_companies_get_separate_default_warehouses()
    {
        // Create first company
        $companyData1 = [
            'name' => 'Company One',
            'document_number' => '11111111111',
            'email' => 'one@company.com',
            'phone' => '1111111111',
            'address' => '111 First Street',
            'city' => 'First City',
            'state' => 'First State',
            'postal_code' => '11111',
            'country' => 'First Country',
            'status' => 'active',
        ];

        $request1 = new Request($companyData1);
        $this->companyController->store($request1);

        // Create second company
        $companyData2 = [
            'name' => 'Company Two',
            'document_number' => '22222222222',
            'email' => 'two@company.com',
            'phone' => '2222222222',
            'address' => '222 Second Street',
            'city' => 'Second City',
            'state' => 'Second State',
            'postal_code' => '22222',
            'country' => 'Second Country',
            'status' => 'active',
        ];

        $request2 = new Request($companyData2);
        $this->companyController->store($request2);

        // Get both companies
        $company1 = Company::where('name', 'Company One')->first();
        $company2 = Company::where('name', 'Company Two')->first();

        // Assert both companies have their own warehouses
        $warehouse1 = Warehouse::where('company_id', $company1->id)->first();
        $warehouse2 = Warehouse::where('company_id', $company2->id)->first();

        $this->assertNotNull($warehouse1);
        $this->assertNotNull($warehouse2);
        $this->assertNotEquals($warehouse1->id, $warehouse2->id);
        $this->assertEquals($company1->id, $warehouse1->company_id);
        $this->assertEquals($company2->id, $warehouse2->company_id);
    }
}