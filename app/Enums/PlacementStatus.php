<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class PlacementStatus extends Enum
{
    public const Unspecified = 0;
    public const Open = 1;
    public const Confirmed = 2;
    public const Cancelled = 3;
}
