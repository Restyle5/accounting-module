<?php

use App\Services\JournalEntryService;

beforeEach(function () {
    $this->service = new JournalEntryService();
});

it('returns true when debits equal credits', function () {
    $lines = [
        ['type' => 'debit',  'amount' => 1000.00],
        ['type' => 'credit', 'amount' => 1000.00],
    ];

    expect($this->service->validateBalance($lines))->toBeTrue();
});

it('returns false when debits do not equal credits', function () {
    $lines = [
        ['type' => 'debit',  'amount' => 1000.00],
        ['type' => 'credit', 'amount' => 500.00],
    ];

    expect($this->service->validateBalance($lines))->toBeFalse();
});

it('returns false when there are only debits', function () {
    $lines = [
        ['type' => 'debit', 'amount' => 1000.00],
        ['type' => 'debit', 'amount' => 1000.00],
    ];

    expect($this->service->validateBalance($lines))->toBeFalse();
});

it('returns false when there are only credits', function () {
    $lines = [
        ['type' => 'credit', 'amount' => 1000.00],
        ['type' => 'credit', 'amount' => 1000.00],
    ];

    expect($this->service->validateBalance($lines))->toBeFalse();
});

it('handles floating point amounts correctly', function () {
    $lines = [
        ['type' => 'debit',  'amount' => 1000.10],
        ['type' => 'credit', 'amount' => 1000.10],
    ];

    expect($this->service->validateBalance($lines))->toBeTrue();
});

it('returns false when lines are empty', function () {
    expect($this->service->validateBalance([]))->toBeFalse();
});

it('handles multiple debit and credit lines that balance', function () {
    $lines = [
        ['type' => 'debit',  'amount' => 500.00],
        ['type' => 'debit',  'amount' => 500.00],
        ['type' => 'credit', 'amount' => 600.00],
        ['type' => 'credit', 'amount' => 400.00],
    ];

    expect($this->service->validateBalance($lines))->toBeTrue();
});

it('returns false when multiple lines do not balance', function () {
    $lines = [
        ['type' => 'debit',  'amount' => 500.00],
        ['type' => 'debit',  'amount' => 500.00],
        ['type' => 'credit', 'amount' => 600.00],
        ['type' => 'credit', 'amount' => 300.00],
    ];

    expect($this->service->validateBalance($lines))->toBeFalse();
});