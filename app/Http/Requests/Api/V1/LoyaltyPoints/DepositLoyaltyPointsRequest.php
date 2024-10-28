<?php

namespace App\Http\Requests\Api\V1\LoyaltyPoints;

use App\Enums\AccountType;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepositLoyaltyPointsRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'account_type' => ['required', 'string', Rule::in(AccountType::class)],
            'account_id' => [
                'required', 'int',
                Rule::exists(LoyaltyAccount::class, $this->input('account_type'))
                    ->where('active', true)
            ],
            'loyalty_points_rule' => [
                'required', 'string',
                Rule::exists(LoyaltyPointsRule::class, 'points_rule')
            ],
            'description' => ['required', 'string'],
            'payment_id' => ['required', 'string'],
            'payment_amount' => ['required', 'numeric'],
            'payment_time' => ['required', 'date'],
        ];
    }

}
