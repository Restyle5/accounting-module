<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'code'      => $this->faker->unique()->numerify('####'),
            'name'      => $this->faker->words(3, true),
            'type'      => $this->faker->randomElement(AccountType::cases())->value,
            'is_active' => true,
        ];
    }

    public function asset(): static
    {
        return $this->state(['type' => AccountType::Asset->value]);
    }

    public function liability(): static
    {
        return $this->state(['type' => AccountType::Liability->value]);
    }

    public function equity(): static
    {
        return $this->state(['type' => AccountType::Equity->value]);
    }

    public function revenue(): static
    {
        return $this->state(['type' => AccountType::Revenue->value]);
    }

    public function expense(): static
    {
        return $this->state(['type' => AccountType::Expense->value]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}