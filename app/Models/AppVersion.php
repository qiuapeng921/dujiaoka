<?php

namespace App\Models;


use App\Events\GoodsDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVersion extends BaseModel
{
    use SoftDeletes;

    protected $table = 'app_version';
}
