<?php

namespace App\Enums;

enum EarningStatus: string
{
    case Processed = 'processed';
    case Cancelled = 'cancelled';
}
