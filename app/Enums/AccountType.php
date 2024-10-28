<?php

namespace App\Enums;

enum AccountType: string
{
    case Phone = 'phone';
    case Card = 'card';
    case Email = 'email';
}
