<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoyaltyPoints\CancelLoyaltyPointsRequest;
use App\Http\Requests\Api\V1\LoyaltyPoints\DepositLoyaltyPointsRequest;
use App\Http\Requests\Api\V1\LoyaltyPoints\WithdrawLoyaltyPointsRequest;
use App\Http\Resources\Api\V1\LoyaltyPointsTransactionResource;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;
use App\Notifications\LoyaltyPointsDepositNotification;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoyaltyPointsController extends Controller
{
    public function deposit(DepositLoyaltyPointsRequest $request): LoyaltyPointsTransactionResource
    {
        // Хз, зачем вообще это тут надо. По хорошему логировать по другому
        Log::info('Deposit transaction input', $request->validated());

        $account = LoyaltyAccount::query()
            ->where($request->account_type, '=', $request->account_id)
            ->where('active', true)
            ->firstOrFail();

        $transaction = LoyaltyPointsTransaction::performPaymentLoyaltyPoints(
            $account->id,
            $request->loyalty_points_rule,
            $request->description,
            $request->payment_id,
            $request->payment_amount,
            Carbon::parse($request->payment_time)
        );

        Log::info($transaction); // Синхронные логи - плохо!!

        $account->notify(
            new LoyaltyPointsDepositNotification($transaction->points_amount, $account->getBalance())
        );

        return new LoyaltyPointsTransactionResource($transaction);
    }

    public function cancel(CancelLoyaltyPointsRequest $request): Response
    {
        LoyaltyPointsTransaction::query()
            ->where('id', '=', $request->transaction_id)
            ->where('canceled', 0)
            ->update([
                'canceled' => now()->timestamp,
                'cancellation_reason' => $request->cancellation_reason,
            ]);

        return response()->noContent();
    }

    public function withdraw(WithdrawLoyaltyPointsRequest $request)
    {

        Log::info('Withdraw loyalty points transaction input', $request->validated());

        $account = LoyaltyAccount::query()
            ->where($request->account_type, '=', $request->account_id)
            ->where('active', true)
            ->firstOrFail();

        if ($account->getBalance() < $request->points_amount) {
            Log::info('Insufficient funds', $request->validated());
            throw ValidationException::withMessages(['points_amount' => 'Insufficient funds']);
        }

        $transaction = LoyaltyPointsTransaction::withdrawLoyaltyPoints($account->id, $request->points_amount, $request->description);
        Log::info('Withdraw loyalty points transaction output', $transaction);

        return new LoyaltyPointsTransactionResource($transaction);
    }
}
