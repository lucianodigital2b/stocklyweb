<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Store a newly created company in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document_number' => 'required|string|max:255|unique:companies,document_number',
            'email' => 'required|email|max:255|unique:companies,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive,suspended',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company = Company::create([
                'name' => $request->name,
                'document_number' => $request->document_number,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'status' => $request->status ?? 'active',
                'settings' => $request->settings ?? [],
            ]);

            // Associate the current user with the company
            $user = $request->user();
            $user->company_id = $company->id;
            $user->save();

            // Create a default store for the company
            $store = Store::create([
                'name' => $company->name . ' Store',
                'domain' => strtolower(str_replace(' ', '', $company->name)) . '.store',
                'company_id' => $company->id,
            ]);

            return response()->json([
                'message' => 'Company and store created successfully',
                'data' => [
                    'company' => $company,
                    'store' => $store
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified company.
     *
     * @param Company $company
     * @return JsonResponse
     */
    public function show(Company $company): JsonResponse
    {
        return response()->json([
            'data' => $company
        ]);
    }

    /**
     * Update the specified company in storage.
     *
     * @param Request $request
     * @param Company $company
     * @return JsonResponse
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'document_number' => 'sometimes|required|string|max:255|unique:companies,document_number,' . $company->id,
            'email' => 'sometimes|required|email|max:255|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'city' => 'sometimes|required|string|max:255',
            'state' => 'sometimes|required|string|max:255',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:255',
            'status' => 'nullable|in:active,inactive,suspended',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company->update($request->only([
                'name', 'document_number', 'email', 'phone', 'address',
                'city', 'state', 'postal_code', 'country', 'status', 'settings'
            ]));

            return response()->json([
                'message' => 'Company updated successfully',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}