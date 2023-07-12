<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class AgencyModel extends MorphPivot
{
    protected $morphType = 'model_type';
}
