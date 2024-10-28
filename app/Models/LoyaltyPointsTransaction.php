<?php

namespace App\Models;

use App\Enums\AccrualType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPointsTransaction extends Model
{
    protected $table = 'loyalty_points_transaction';

    protected $fillable = [
        'account_id',
        'points_rule',
        'points_amount',
        'description',
        'payment_id',
        'payment_amount',
        'payment_time',
    ];

    protected $casts = [
        'payment_time' => 'datetime',
    ];

    public static function performPaymentLoyaltyPoints(
        int $account_id,
        string $points_rule,
        string $description,
        int $payment_id,
        float $payment_amount,
        Carbon $payment_time
    ): LoyaltyPointsTransaction
    {
        // Вызывать модель из модели плохо. Тестовое говорит, что трогать только контроллер. А так по хорошему надо было бы вынести в сервис
        // Репозиторий все дела 🤷
        $pointsRule = LoyaltyPointsRule::where('points_rule', '=', $points_rule)->first();

        if (!$pointsRule) {
            throw new \InvalidArgumentException('Invalid points rule');
        }

        $points_amount = match ($pointsRule->accrual_type) {
            AccrualType::RelativeRate => ($payment_amount / 100) * $pointsRule->accrual_value,
            AccrualType::AbsolutePointsAmount => $pointsRule->accrual_value,
            default => throw new \InvalidArgumentException("Accrual type {$pointsRule->accrual_type} is not supported"),
        };

        return LoyaltyPointsTransaction::create([
            'account_id' => $account_id,
            'points_rule' => $pointsRule->id,
            'points_amount' => $points_amount,
            'description' => $description,
            'payment_id' => $payment_id,
            'payment_amount' => $payment_amount,
            'payment_time' => $payment_time,
        ]);
    }

    public static function withdrawLoyaltyPoints($account_id, $points_amount, $description) {
        return LoyaltyPointsTransaction::create([
            'account_id' => $account_id,
            'points_rule' => 'withdraw',
            'points_amount' => -$points_amount,
            'description' => $description,
        ]);
    }
}
