<?php

namespace App\Enums;

enum AssetType: string
{
    case Currency = 'currency';
    case Crypto = 'crypto';
    case Share = 'share';
    case Commodity = 'commodity';
}
