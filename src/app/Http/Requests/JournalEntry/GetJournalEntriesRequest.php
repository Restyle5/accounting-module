<?php

namespace App\Http\Requests\JournalEntry;

use App\Enums\JournalLineType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GetJournalEntriesRequest extends FormRequest
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
        return [
            'date_from'   => ['sometimes', 'date'],
            'date_to'     => ['sometimes', 'date', 'after_or_equal:date_from'],
            'reference'   => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
