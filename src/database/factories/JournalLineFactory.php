<?php

namespace Database\Factories;

use App\Enums\JournalLineType;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalLine>
 */
class JournalLineFactory extends Factory
{
    protected $model = JournalLine::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journal_entry_id' => JournalEntry::factory(),
            'account_id'       => Account::factory(),
            'type'             => $this->faker->randomElement(JournalLineType::cases())->value,
            'amount'           => $this->faker->randomFloat(2, 100, 10000),
        ];
    }

    public function debit(): static
    {
        return $this->state(['type' => JournalLineType::Debit->value]);
    }

    public function credit(): static
    {
        return $this->state(['type' => JournalLineType::Credit->value]);
    }
}
