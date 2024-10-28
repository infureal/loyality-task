<?php

namespace App\Models;

use App\Enums\AccrualType;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPointsRule extends Model
{

    protected $table = 'loyalty_points_rule';

    protected $fillable = [
        'points_rule',
        'accrual_type',
        'accrual_value',
    ];

    protected $casts = [
        'accrual_type' => AccrualType::class,
    ];

}
