<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        $user     = User::first();
        $cash     = Account::where('code', '1000')->first();
        $revenue  = Account::where('code', '4000')->first();
        $rent     = Account::where('code', '5000')->first();
        $salaries = Account::where('code', '5100')->first();

        // Entry 1 — Cash sales
        $entry1 = JournalEntry::create([
            'date'        => now()->format('Y-m-d'),
            'reference'   => 'JV-0001',
            'description' => 'Cash sales for the day',
            'created_by'  => $user->id,
        ]);

        $entry1->lines()->createMany([
            ['account_id' => $cash->id,    'type' => 'debit',  'amount' => 5000.00],
            ['account_id' => $revenue->id, 'type' => 'credit', 'amount' => 5000.00],
        ]);

        // Entry 2 — Rent payment
        $entry2 = JournalEntry::create([
            'date'        => now()->format('Y-m-d'),
            'reference'   => 'JV-0002',
            'description' => 'Monthly rent payment',
            'created_by'  => $user->id,
        ]);

        $entry2->lines()->createMany([
            ['account_id' => $rent->id, 'type' => 'debit',  'amount' => 2000.00],
            ['account_id' => $cash->id, 'type' => 'credit', 'amount' => 2000.00],
        ]);

        // Entry 3 — Salaries
        $entry3 = JournalEntry::create([
            'date'        => now()->format('Y-m-d'),
            'reference'   => 'JV-0003',
            'description' => 'Monthly salaries',
            'created_by'  => $user->id,
        ]);

        $entry3->lines()->createMany([
            ['account_id' => $salaries->id, 'type' => 'debit',  'amount' => 8000.00],
            ['account_id' => $cash->id,     'type' => 'credit', 'amount' => 8000.00],
        ]);
    }
}