<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;

beforeEach(function () {
    actingAsUser();
});

it('returns trial balance with correct structure', function () {
    $response = $this->getJson('/api/reports/trial-balance');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'accounts',
            'totals' => ['total_debit', 'total_credit'],
        ]);
});

it('returns all accounts even with no journal lines', function () {
    Account::factory(5)->create();

    $response = $this->getJson('/api/reports/trial-balance');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'accounts');
});

it('total debits always equal total credits', function () {
    $user    = User::factory()->create();
    $cash    = Account::factory()->create(['type' => 'asset']);
    $revenue = Account::factory()->create(['type' => 'revenue']);

    $entry = JournalEntry::factory()->create(['created_by' => $user->id]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $cash->id,
        'type'             => 'debit',
        'amount'           => 5000.00,
    ]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $revenue->id,
        'type'             => 'credit',
        'amount'           => 5000.00,
    ]);

    $response = $this->getJson('/api/reports/trial-balance');

    $response->assertStatus(200);

    $totals = $response->json('totals');

    expect($totals['total_debit'])->toBe($totals['total_credit']);
});

it('calculates correct debit and credit totals per account', function () {
    $user    = User::factory()->create();
    $cash    = Account::factory()->create(['code' => '1000', 'type' => 'asset']);
    $revenue = Account::factory()->create(['code' => '4000', 'type' => 'revenue']);

    $entry = JournalEntry::factory()->create(['created_by' => $user->id]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $cash->id,
        'type'             => 'debit',
        'amount'           => 3000.00,
    ]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $revenue->id,
        'type'             => 'credit',
        'amount'           => 3000.00,
    ]);

    $response = $this->getJson('/api/reports/trial-balance');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'code'         => '1000',
            'total_debit'  => 3000.0,
            'total_credit' => 0.0,
            'balance'      => 3000.0,
        ])
        ->assertJsonFragment([
            'code'         => '4000',
            'total_debit'  => 0.0,
            'total_credit' => 3000.0,
            'balance'      => -3000.0,
        ]);
});

it('filters trial balance by date range', function () {
    $user    = User::factory()->create();
    $cash    = Account::factory()->create(['code' => '1000', 'type' => 'asset']);
    $revenue = Account::factory()->create(['code' => '4000', 'type' => 'revenue']);

    // Entry within range
    $entryIn = JournalEntry::factory()->create([
        'created_by' => $user->id,
        'date'       => '2024-06-15',
    ]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entryIn->id,
        'account_id'       => $cash->id,
        'type'             => 'debit',
        'amount'           => 3000.00,
    ]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entryIn->id,
        'account_id'       => $revenue->id,
        'type'             => 'credit',
        'amount'           => 3000.00,
    ]);

    // Entry outside range
    $entryOut = JournalEntry::factory()->create([
        'created_by' => $user->id,
        'date'       => '2023-01-01',
    ]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entryOut->id,
        'account_id'       => $cash->id,
        'type'             => 'debit',
        'amount'           => 9000.00,
    ]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entryOut->id,
        'account_id'       => $revenue->id,
        'type'             => 'credit',
        'amount'           => 9000.00,
    ]);

    $response = $this->getJson('/api/reports/trial-balance?date_from=2024-01-01&date_to=2024-12-31');

    $response->assertStatus(200);

    $totals = $response->json('totals');

    expect($totals['total_debit'])->toEqual(3000.0)
        ->and($totals['total_credit'])->toEqual(3000.0);
});

it('returns zero totals when no journal lines exist', function () {
    $response = $this->getJson('/api/reports/trial-balance');

    $response->assertStatus(200);

    $totals = $response->json('totals');

    expect($totals['total_debit'])->toEqual(0.0)
        ->and($totals['total_credit'])->toEqual(0.0);
});

it('returns 422 when date_to is before date_from', function () {
    $response = $this->getJson('/api/reports/trial-balance?date_from=2024-12-31&date_to=2024-01-01');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date_to']);
});


// Auth is handled in beforeEach via actingAsUser, tested separately in AuthTest'
