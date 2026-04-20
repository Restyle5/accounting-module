<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use App\Enums\AccountType;

beforeEach(function () {
    actingAsUser();
});

// Index
it('can list all accounts', function () {
    Account::factory(5)->create();

    $response = $this->getJson('/api/accounts');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});


it('returns empty array when no accounts exist', function () {
    $response = $this->getJson('/api/accounts');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

// Store
it('can create an account', function () {
    $response = $this->postJson('/api/accounts', [
        'code' => '9000',
        'name' => 'Test Account',
        'type' => AccountType::Asset->value,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['code' => '9000', 'name' => 'Test Account']);

    $this->assertDatabaseHas('accounts', ['code' => '9000']);
});

it('cannot create account with duplicate code', function () {
    Account::factory()->create(['code' => '9000']);

    $response = $this->postJson('/api/accounts', [
        'code' => '9000',
        'name' => 'Duplicate Account',
        'type' => AccountType::Asset->value,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

it('cannot create account with invalid type', function () {
    $response = $this->postJson('/api/accounts', [
        'code' => '9001',
        'name' => 'Bad Type Account',
        'type' => 'invalid_type',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

it('cannot create account without required fields', function () {
    $response = $this->postJson('/api/accounts', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code', 'name', 'type']);
});

it('creates account with is_active true by default', function () {
    $response = $this->postJson('/api/accounts', [
        'code' => '9002',
        'name' => 'Active Account',
        'type' => AccountType::Asset->value,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['is_active' => true]);
});

// Show
it('can show a single account', function () {
    $account = Account::factory()->create();

    $response = $this->getJson("/api/accounts/{$account->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $account->id]);
});

it('returns 404 for non existent account', function () {
    $response = $this->getJson('/api/accounts/999');

    $response->assertStatus(404);
});

// Update
it('can update an account', function () {
    $account = Account::factory()->create();

    $response = $this->putJson("/api/accounts/{$account->id}", [
        'code' => $account->code,
        'name' => 'Updated Name',
        'type' => $account->type,
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated Name']);
});

it('can update account with same code', function () {
    $account = Account::factory()->create(['code' => '8000']);

    $response = $this->putJson("/api/accounts/{$account->id}", [
        'code' => '8000',
        'name' => 'Updated Name',
        'type' => $account->type,
    ]);

    $response->assertStatus(200);
});

it('cannot update account code to an existing code', function () {
    $accountA = Account::factory()->create(['code' => '8000']);
    $accountB = Account::factory()->create(['code' => '8001']);

    $response = $this->putJson("/api/accounts/{$accountB->id}", [
        'code' => '8000',
        'name' => $accountB->name,
        'type' => $accountB->type,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

it('returns 404 when updating non existent account', function () {
    $response = $this->putJson('/api/accounts/999', [
        'code' => '9999',
        'name' => 'Ghost Account',
        'type' => AccountType::Asset->value,
    ]);

    $response->assertStatus(404);
});

// Delete
it('can delete (soft) an account with no journal lines', function () {
    $account = Account::factory()->create();

    $response = $this->deleteJson("/api/accounts/{$account->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Account deleted successfully.']);

    $this->assertSoftDeleted('accounts', ['id' => $account->id]);
});

it('cannot delete (soft) account that has journal lines', function () {
    $user    = User::factory()->create();
    $account = Account::factory()->create();
    $entry   = JournalEntry::factory()->create(['created_by' => $user->id]);

    JournalLine::factory()->create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $account->id,
    ]);

    $response = $this->deleteJson("/api/accounts/{$account->id}");

    $response->assertStatus(422)
        ->assertJson(['message' => 'Cannot delete account with existing journal entries.']);

    // Record should still exist and NOT be soft deleted
    $this->assertNotSoftDeleted('accounts', ['id' => $account->id]);
});

it('returns 404 when deleting non existent account', function () {
    $response = $this->deleteJson('/api/accounts/999');

    $response->assertStatus(404);
});

// Unauthenticated
it('returns 401 when not authenticated', function () {
    $response = $this->getJson('/api/accounts');

    $response->assertStatus(401);
})->skip('actingAsUser is called in beforeEach, test auth separately in AuthTest');
