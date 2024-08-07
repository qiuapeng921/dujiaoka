<?php

namespace App\Jobs;

use App\Models\Users;
use App\Models\SystemConfig;
use Illuminate\Bus\Queueable;
use App\Models\UserBalanceRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OperateBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 任务运行的超时时间。
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * @var int
     */
    private $userId;

    /**
     * 1 注册 2增减
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $balance;

    /**
     * @var bool
     */
    private $isAdd;

    /**
     * @var string
     */
    private $remark;

    public function __construct(int $userId, int $type, int $balance = 0, bool $isAdd = false, $remark = '')
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->balance = $balance;
        $this->isAdd = $isAdd;
        $this->remark = $remark;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->type) {
            case 1:
                $this->register();
                break;
            case 2:
                $this->addOrSub();
                break;
        }
    }

    public function register()
    {
        // 获取系统配置是否注册送积分
        $sysConfig = SystemConfig::query()->where('type', 1)->first();
        // 判断是否开启注册送积分
        if ($sysConfig && $sysConfig->status == 1) {
            $value = $sysConfig['value'];
            if ($value > 0) {
                $user = Users::query()->where('id', $this->userId)->first();
                DB::beginTransaction();
                try {
                    // 增加用户积分
                    Users::query()->where('id', $this->userId)->increment('balance', $value);
                    // 添加用户积分操作记录
                    UserBalanceRecord::query()->insert([
                        'user_id'        => $this->userId,
                        'before_balance' => $user['balance'],
                        'after_balance'  => $user['balance'] + $value,
                        'type'           => 1,
                        'remark'         => '新用户注册则送积分',
                        'created_at'     => date("Y-m-d H:i:s"),
                        'updated_at'     => date("Y-m-d H:i:s"),
                    ]);
                    DB::commit();
                } catch (\Exception $exception) {
                    logs()->error("注册添加用户积分异常:", [$exception->getMessage(), $this->userId]);
                    DB::rollBack();
                }
            }
        }
    }


    public function addOrSub()
    {
        $user = Users::query()->where('id', $this->userId)->first();
        DB::beginTransaction();
        try {
            if ($this->isAdd) {
                // 增加用户积分
                Users::query()->where('id', $this->userId)->increment('balance', $this->balance);
                // 添加用户积分操作记录
                UserBalanceRecord::query()->insert([
                    'user_id'        => $this->userId,
                    'before_balance' => $user['balance'],
                    'after_balance'  => $user['balance'] + $this->balance,
                    'type'           => 1,
                    'remark'         => $this->remark,
                    'created_at'     => date("Y-m-d H:i:s"),
                    'updated_at'     => date("Y-m-d H:i:s"),
                ]);
            } else {
                // 减少用户积分
                Users::query()->where('id', $this->userId)->decrement('balance', $this->balance);
                UserBalanceRecord::query()->insert([
                    'user_id'        => $this->userId,
                    'before_balance' => $user['balance'],
                    'after_balance'  => $user['balance'] - $this->balance,
                    'type'           => 0,
                    'remark'         => $this->remark,
                    'created_at'     => date("Y-m-d H:i:s"),
                    'updated_at'     => date("Y-m-d H:i:s"),
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            logs()->error("处理用户积分异常:", [$exception->getMessage(), $this->userId, $this->balance, $this->isAdd]);
            DB::rollBack();
        }
    }
}
