<?php

namespace App\Enums;

enum EarningFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
}
