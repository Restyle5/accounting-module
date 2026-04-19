<?php

namespace App\Http\Controllers;

use App\Services\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrialBalanceController extends Controller
{
    public function __construct(protected TrialBalanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => ['sometimes', 'date'],
            'date_to'   => ['sometimes', 'date', 'after_or_equal:date_from'],
        ]);

        $data = $this->service->generate($request->only('date_from', 'date_to'));

        return response()->json($data);
    }
}
