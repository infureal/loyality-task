<?php

namespace App\Models;

use App\Mail\AccountActivated;
use App\Mail\AccountDeactivated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoyaltyAccount extends Model
{
    use Notifiable;

    protected $table = 'loyalty_account';

    protected $fillable = [
        'phone',
        'card',
        'email',
        'email_notification',
        'phone_notification',
        'active',
    ];

    protected $casts = [
        'email_notification' => 'boolean',
        'phone_notification' => 'boolean',
        'active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointsTransaction::class, 'account_id', 'id');
    }

    public function getBalance(): float
    {
        // Вообще вызывать обращение к другой модели из модели - это плохо
        return $this->transactions()
            ->where('canceled', '=', 0)
            ->sum('points_amount');
    }

    public function notifyAboutActuveStatus()
    {
        if ($this->email != '' && $this->email_notification) {
            if ($this->active) {
                Mail::to($this)->send(new AccountActivated($this->getBalance()));
            } else {
                Mail::to($this)->send(new AccountDeactivated());
            }
        }

        if ($this->phone != '' && $this->phone_notification) {
            // instead SMS component
            Log::info('Account: phone: ' . $this->phone . ' ' . ($this->active ? 'Activated' : 'Deactivated'));
        }
    }
}
