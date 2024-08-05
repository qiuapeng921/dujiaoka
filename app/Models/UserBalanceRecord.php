<?php

namespace App\Models;


use App\Events\GoodsDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBalanceRecord extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_balance_record';
}
