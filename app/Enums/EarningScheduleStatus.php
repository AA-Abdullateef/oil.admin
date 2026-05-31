<?php

namespace App\Enums;

enum EarningScheduleStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
}
