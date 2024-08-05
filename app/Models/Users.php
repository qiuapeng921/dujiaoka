<?php

namespace App\Models;


use App\Events\GoodsDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends BaseModel
{
    use SoftDeletes;

    protected $table = 'users';
}
