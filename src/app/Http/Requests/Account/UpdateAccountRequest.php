<?php

namespace App\Http\Requests\Account;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;


class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // returns true for now.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $accountId = $this->route('account');

        return [
            'code'      => ['required', 'string', 'max:20', Rule::unique(app(Account::class)->getTable(), 'code')->ignore($accountId)],
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', new Enum(AccountType::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
