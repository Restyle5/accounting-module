<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Collection;

class TrialBalanceService
{
    public function generate(array $filters = []): array
    {
        $accounts = Account::query()
            ->with(['journalLines' => function ($query) use ($filters) {
                $query->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id');

                if (!empty($filters['date_from'])) {
                    $query->whereDate('journal_entries.date', '>=', $filters['date_from']);
                }

                if (!empty($filters['date_to'])) {
                    $query->whereDate('journal_entries.date', '<=', $filters['date_to']);
                }
            }])
            ->orderBy('code')
            ->get();

        $result = $accounts->map(function (Account $account) {
            $totalDebit  = $account->journalLines->where('type', 'debit')->sum('amount');
            $totalCredit = $account->journalLines->where('type', 'credit')->sum('amount');
            $balance     = $totalDebit - $totalCredit;

            return [
                'code'         => $account->code,
                'name'         => $account->name,
                'type'         => $account->type,
                'total_debit'  => (float) round($totalDebit, 2),
                'total_credit' => (float) round($totalCredit, 2),
                'balance'      => (float) round($balance, 2),
            ];
        });

        $totals = [
            'total_debit'  => (float) round($result->sum('total_debit'), 2),
            'total_credit' => (float) round($result->sum('total_credit'), 2),
        ];

        return [
            'accounts' => $result->values(),
            'totals'   => $totals,
        ];
    }
}
