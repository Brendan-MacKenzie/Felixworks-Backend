<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class SectorModel extends MorphPivot
{
    protected $morphType = 'model_type';
}
