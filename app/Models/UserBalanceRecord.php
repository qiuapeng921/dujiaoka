<?php

namespace App\Models;


use App\Events\GoodsDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBalanceRecord extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_balance_record';

    // 变更类型
    const TYPE_SUB = 0; // 减少
    const TYPE_ADD = 1; // 增加

    public static function getTypeMap(): array
    {
        return [
            self::TYPE_SUB => '减少',
            self::TYPE_ADD => '增加',
        ];
    }
}
