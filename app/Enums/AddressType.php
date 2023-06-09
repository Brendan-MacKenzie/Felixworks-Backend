<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class AddressType extends Enum
{
    public const Unspecified = 0;
    public const Default = 1;
    public const Created = 2;
}
