<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    public function validateBalance(array $lines): bool
    {
        if (empty($lines)) {
            return false;
        }

        $debits  = collect($lines)->where('type', 'debit')->sum('amount');
        $credits = collect($lines)->where('type', 'credit')->sum('amount');

        return round($debits, 2) === round($credits, 2);
    }

    public function store(array $data, ?int $userId = null): JournalEntry
    {
        return DB::transaction(function () use ($data, $userId) {

            $entryDetails = [
                'date'        => $data['date'],
                'reference'   => $data['reference'],
                'description' => $data['description'],
            ];

            if ($userId != null) $entryDetails['created_by'] = $userId;

            # Create a Journal Instance.
            $entry = JournalEntry::create($entryDetails);

            # Create Journal lines.
            foreach ($data['lines'] as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'type'       => $line['type'],
                    'amount'     => $line['amount'],
                ]);
            }

            return $entry->load('lines.account');
        });
    }
}
