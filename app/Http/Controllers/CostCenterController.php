<?php

namespace App\Http\Controllers;

use App\Http\Requests\CostCenterRequest;
use App\Models\CostCenter;
use App\Services\CostCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    protected $costCenterService;

    public function __construct(CostCenterService $costCenterService)
    {
        $this->costCenterService = $costCenterService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $args = [
            'limit' => $request->input('per_page', 10)
        ];
        
        if(!empty($request->input('q'))){
            $args['q'] = $request->input('q');
        }

        $costCenters = $this->costCenterService->list($args);
        
        return response()->json($costCenters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CostCenterRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CostCenterRequest $request): JsonResponse
    {
        $costCenter = $this->costCenterService->createCostCenter($request->validated());
        
        return response()->json($costCenter, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CostCenter $costCenter): JsonResponse
    {
        $costCenter = $this->costCenterService->getCostCenter($costCenter->id);
        
        return response()->json($costCenter);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CostCenter $costCenter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CostCenterRequest  $request
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CostCenterRequest $request, CostCenter $costCenter): JsonResponse
    {
        $costCenter = $this->costCenterService->updateCostCenter($costCenter->id, $request->validated());
        
        return response()->json($costCenter);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CostCenter  $costCenter
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CostCenter $costCenter): JsonResponse
    {
        $this->costCenterService->deleteCostCenter($costCenter->id);
        
        return response()->json(null, 204);
    }
}
