<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class RepeatType extends Enum
{
    public const Never = 0;
    public const Daily = 1;
    public const Weekly = 2;
}
