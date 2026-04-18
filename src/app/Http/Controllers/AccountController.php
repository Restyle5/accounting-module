<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = Account::orderBy('code')->get();

        return response()->json($accounts);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json($account, 201);
    }

    public function show(Account $account): JsonResponse
    {
        return response()->json($account);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());

        return response()->json($account);
    }

    public function destroy(Account $account): JsonResponse
    {
        // Prevent delete if account has journal lines
        if ($account->journalLines()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with existing journal entries.',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}