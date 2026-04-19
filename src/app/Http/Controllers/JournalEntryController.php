<?php

namespace App\Http\Controllers;

use App\Http\Requests\JournalEntry\GetJournalEntriesRequest;
use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use App\Http\Requests\JournalEntry\StoreJournalEntryRequest;
use App\Http\Requests\JournalEntry\UpdateJournalEntryRequest;
use Illuminate\Http\JsonResponse;

class JournalEntryController extends Controller
{
    public function __construct(protected JournalEntryService $service) {}

    public function index(GetJournalEntriesRequest $request): JsonResponse
    {

        // Validate request.
        $request->validated();

        $entries = JournalEntry::query()
            ->with('lines.account', 'createdBy')
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->date_to);
            })
            ->when($request->filled('reference'), function ($query) use ($request) {
                $query->where('reference', 'like', '%' . $request->reference . '%');
            })
            ->when($request->filled('description'), function ($query) use ($request) {
                $query->where('description', 'like', '%' . $request->description . '%');
            })
            ->orderBy('date', 'desc')
            ->paginate(20);

        return response()->json($entries);
    }

    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        if (!$this->service->validateBalance($request->lines)) {
            return response()->json([
                'message' => 'Total debits must equal total credits.',
            ], 422);
        }

        $entry = $this->service->store($request->validated());

        return response()->json($entry, 201);
    }

    public function show(JournalEntry $journalEntry): JsonResponse
    {
        return response()->json(
            $journalEntry->load('lines.account', 'createdBy')
        );
    }
}
