<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash',                'type' => AccountType::Asset->value],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => AccountType::Asset->value],
            ['code' => '1200', 'name' => 'Inventory',           'type' => AccountType::Asset->value],
            ['code' => '1500', 'name' => 'Fixed Assets',        'type' => AccountType::Asset->value],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable',    'type' => AccountType::Liability->value],
            ['code' => '2100', 'name' => 'Accrued Expenses',    'type' => AccountType::Liability->value],
            ['code' => '2200', 'name' => 'Loans Payable',       'type' => AccountType::Liability->value],

            // Equity
            ['code' => '3000', 'name' => 'Owner Equity',        'type' => AccountType::Equity->value],
            ['code' => '3100', 'name' => 'Retained Earnings',   'type' => AccountType::Equity->value],

            // Revenue
            ['code' => '4000', 'name' => 'Sales Revenue',       'type' => AccountType::Revenue->value],
            ['code' => '4100', 'name' => 'Service Revenue',     'type' => AccountType::Revenue->value],

            // Expenses
            ['code' => '5000', 'name' => 'Rent Expense',        'type' => AccountType::Expense->value],
            ['code' => '5100', 'name' => 'Salaries Expense',    'type' => AccountType::Expense->value],
            ['code' => '5200', 'name' => 'Utilities Expense',   'type' => AccountType::Expense->value],
            ['code' => '5300', 'name' => 'Office Supplies',     'type' => AccountType::Expense->value],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
