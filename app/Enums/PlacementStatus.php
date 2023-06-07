<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class PlacementStatus extends Enum
{
    const Unspecified = 0;
    const Open = 1;
    const Confirmed = 2;
    const Cancelled = 3;
}
