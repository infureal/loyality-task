<?php

namespace App\Http\Requests\Api\V1\LoyaltyPoints;

use App\Models\LoyaltyPointsTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelLoyaltyPointsRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'transaction_id' => [
                'required', 'int',
                Rule::exists(LoyaltyPointsTransaction::class, 'id')
                    ->where('canceled', 0)
            ]
        ];
    }

}
