<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryRequest;
use App\Models\Entry;
use App\Services\EntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    protected $entryService;

    public function __construct(EntryService $entryService)
    {
        $this->entryService = $entryService;
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

        if($request->has('operation')){
            $args['operation'] = $request->input('operation');
        }

        if($request->has('status')){
            $args['status'] = $request->input('status');
        }

        if($request->has('due_at_from')){
            $args['due_at_from'] = $request->input('due_at_from');
            $args['due_at_to'] = $request->input('due_at_to', $request->input('due_at_from'));
        }

        $entries = $this->entryService->list($args);
        
        return response()->json($entries);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Entry::class);

        $entry = new Entry;

        $query = [
            'where' => [
                [
                    'field' => 'company_id',
                    'operator' => '=',
                    'value' => auth()->user()->company_id,
                ],
            ],
        ];

        $services = $this->serviceRepository->list($query);
        $suppliers = $this->supplierRepository->list($query);
        $costCenters = $this->costCenterRepository->list($query);

        return view('modules.entries.create', compact('entry', 'services', 'suppliers', 'costCenters'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\EntryRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EntryRequest $request): JsonResponse
    {
        $entry = $this->entryService->createEntry($request->validated());
        
        return response()->json($entry, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Entry  $entry
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Entry $entry): JsonResponse
    {
        $entry = $this->entryService->getEntry($entry->id);
        
        return response()->json($entry);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\EntryRequest  $request
     * @param  \App\Models\Entry  $entry
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EntryRequest $request, Entry $entry): JsonResponse
    {
        $entry = $this->entryService->updateEntry($entry->id, $request->validated());
        
        return response()->json($entry);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Entry  $entry
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Entry $entry): JsonResponse
    {
        $this->entryService->deleteEntry($entry->id);
        
        return response()->json(null, 204);
    }

    public function receipt(Request $request, Entry $entry)
    {
        if ($entry->company_id != auth()->user()->company_id) {
            abort(403);
        }

        return view('modules.entries.receipt', compact('entry'));
    }
}
