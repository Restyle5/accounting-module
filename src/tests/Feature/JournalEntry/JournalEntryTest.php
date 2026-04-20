<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;

beforeEach(function () {
    actingAsUser();

    $this->cash    = Account::factory()->create(['code' => '1000', 'type' => 'asset']);
    $this->revenue = Account::factory()->create(['code' => '4000', 'type' => 'revenue']);
});

function balancedLines(int $cashId, int $revenueId, float $amount = 1000.00): array
{
    return [
        ['account_id' => $cashId,    'type' => 'debit',  'amount' => $amount],
        ['account_id' => $revenueId, 'type' => 'credit', 'amount' => $amount],
    ];
}

function journalPayload(int $cashId, int $revenueId, string $reference = 'JV-TEST-001'): array
{
    return [
        'date'        => '2024-01-15',
        'reference'   => $reference,
        'description' => 'Test entry',
        'lines'       => balancedLines($cashId, $revenueId),
    ];
}

// Index
it('can list journal entries', function () {
    $user = User::factory()->create();
    JournalEntry::factory(3)->create(['created_by' => $user->id]);

    $response = $this->getJson('/api/journal-entries');

    $response->assertStatus(200);
});

it('can filter journal entries by date range', function () {
    $user = User::factory()->create();

    JournalEntry::factory()->create(['created_by' => $user->id, 'date' => '2024-01-15']);
    JournalEntry::factory()->create(['created_by' => $user->id, 'date' => '2024-06-15']);

    $response = $this->getJson('/api/journal-entries?date_from=2024-01-01&date_to=2024-03-31');

    $response->assertStatus(200)
        ->assertJsonFragment(['date' => '2024-01-15']);
});

it('can search journal entries by reference', function () {
    $user = User::factory()->create();

    JournalEntry::factory()->create(['created_by' => $user->id, 'reference' => 'JV-FIND-ME']);
    JournalEntry::factory()->create(['created_by' => $user->id, 'reference' => 'JV-OTHER']);

    $response = $this->getJson('/api/journal-entries?reference=FIND-ME');

    $response->assertStatus(200)
        ->assertJsonFragment(['reference' => 'JV-FIND-ME']);
});

it('can search journal entries by description', function () {
    $user = User::factory()->create();

    JournalEntry::factory()->create(['created_by' => $user->id, 'description' => 'Monthly rent payment']);
    JournalEntry::factory()->create(['created_by' => $user->id, 'description' => 'Salary payment']);

    $response = $this->getJson('/api/journal-entries?description=rent');

    $response->assertStatus(200)
        ->assertJsonFragment(['description' => 'Monthly rent payment']);
});

it('returns 422 when date_to is before date_from', function () {
    $response = $this->getJson('/api/journal-entries?date_from=2024-12-31&date_to=2024-01-01');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date_to']);
});

// Store
it('can create a balanced journal entry', function () {
    $response = $this->postJson('/api/journal-entries', journalPayload($this->cash->id, $this->revenue->id));

    $response->assertStatus(201)
        ->assertJsonFragment(['reference' => 'JV-TEST-001']);

    $this->assertDatabaseHas('journal_entries', ['reference' => 'JV-TEST-001']);
    $this->assertDatabaseCount('journal_lines', 2);
});

it('cannot create entry when debits do not equal credits', function () {
    $response = $this->postJson('/api/journal-entries', [
        'date'        => '2024-01-15',
        'reference'   => 'JV-TEST-002',
        'description' => 'Unbalanced entry',
        'lines'       => [
            ['account_id' => $this->cash->id,    'type' => 'debit',  'amount' => 1000.00],
            ['account_id' => $this->revenue->id, 'type' => 'credit', 'amount' => 500.00],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Total debits must equal total credits.']);
});

it('cannot create entry with less than 2 lines', function () {
    $response = $this->postJson('/api/journal-entries', [
        'date'        => '2024-01-15',
        'reference'   => 'JV-TEST-003',
        'description' => 'Single line entry',
        'lines'       => [
            ['account_id' => $this->cash->id, 'type' => 'debit', 'amount' => 1000.00],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lines']);
});

it('cannot create entry with invalid account', function () {
    $response = $this->postJson('/api/journal-entries', [
        'date'        => '2024-01-15',
        'reference'   => 'JV-TEST-004',
        'description' => 'Invalid account entry',
        'lines'       => [
            ['account_id' => 9999,               'type' => 'debit',  'amount' => 1000.00],
            ['account_id' => $this->revenue->id, 'type' => 'credit', 'amount' => 1000.00],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lines.0.account_id']);
});

it('cannot create entry with duplicate reference', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->create(['created_by' => $user->id, 'reference' => 'JV-DUPE']);

    $response = $this->postJson('/api/journal-entries', [
        'date'        => '2024-01-15',
        'reference'   => 'JV-DUPE',
        'description' => 'Duplicate reference',
        'lines'       => balancedLines($this->cash->id, $this->revenue->id),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reference']);
});

it('cannot create entry with invalid line type', function () {
    $response = $this->postJson('/api/journal-entries', [
        'date'        => '2024-01-15',
        'reference'   => 'JV-TEST-005',
        'description' => 'Invalid type entry',
        'lines'       => [
            ['account_id' => $this->cash->id,    'type' => 'invalid', 'amount' => 1000.00],
            ['account_id' => $this->revenue->id, 'type' => 'credit',  'amount' => 1000.00],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lines.0.type']);
});

it('cannot create entry without required fields', function () {
    $response = $this->postJson('/api/journal-entries', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date', 'reference', 'description', 'lines']);
});

it('cannot create entry with zero amount', function () {
    $response = $this->postJson('/api/journal-entries', [
        'date'        => '2024-01-15',
        'reference'   => 'JV-TEST-006',
        'description' => 'Zero amount entry',
        'lines'       => [
            ['account_id' => $this->cash->id,    'type' => 'debit',  'amount' => 0],
            ['account_id' => $this->revenue->id, 'type' => 'credit', 'amount' => 0],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lines.0.amount', 'lines.1.amount']);
});

// Show
it('can show a journal entry with lines', function () {
    $user  = User::factory()->create();
    $entry = JournalEntry::factory()->create(['created_by' => $user->id]);

    $response = $this->getJson("/api/journal-entries/{$entry->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['id', 'date', 'reference', 'description', 'lines']);
});

it('returns 404 for non existent journal entry', function () {
    $response = $this->getJson('/api/journal-entries/999');

    $response->assertStatus(404);
});
