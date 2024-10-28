<?php

namespace App\Http\Requests\Api\V1\LoyaltyPoints;

use App\Enums\AccountType;
use App\Models\LoyaltyAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WithdrawLoyaltyPointsRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'account_id' => [
                'required', 'int',
                Rule::exists(LoyaltyAccount::class, $this->input('account_type'))
                    ->where('active', true)
            ],
            'account_type' => ['required', Rule::in(AccountType::class)],
            'points_amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string'],
        ];
    }

}
