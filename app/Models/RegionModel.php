<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class RegionModel extends MorphPivot
{
    protected $morphType = 'model_type';
}
