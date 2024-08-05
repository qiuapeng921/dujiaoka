<?php

namespace App\Models;


use App\Events\GoodsDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemConfig extends BaseModel
{
    use SoftDeletes;

    protected $table = 'system_config';
}
