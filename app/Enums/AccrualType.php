<?php

namespace App\Enums;

enum AccrualType: string
{
    case RelativeRate = 'relative_rate';
    case AbsolutePointsAmount = 'absolute_points_amount';
}
