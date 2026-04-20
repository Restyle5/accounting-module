<?php

namespace App\Http\Requests\JournalEntry;

use App\Enums\JournalLineType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateJournalEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // set it to true, for now.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entryId = $this->route('journal_entry');

        return [
            'date'              => ['required', 'date'],
            'reference'         => ['required', 'string', 'max:100', Rule::unique('journal_entries', 'reference')->ignore($entryId)],
            'description'       => ['required', 'string', 'max:500'],
            'lines'             => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.type'      => ['required', new Enum(JournalLineType::class)],
            'lines.*.amount'    => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
