<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class AddressType extends Enum
{
    public const WorkAddress = 0;
    public const Location = 1;
    public const Office = 2;
}
