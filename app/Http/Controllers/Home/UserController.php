<?php

namespace App\Http\Controllers\Home;

use App\Models\Users;
use App\Jobs\OperateBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

/**
 * 用户控制器
 *
 */
class UserController extends BaseController
{
    /**
     * 操作积分
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function operateBalance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'    => 'required|in:0,1',
            'balance' => 'integer|required',
            'remark'  => 'string',
        ], [
            'type.required'    => "变更类型必填",
            'type.in'          => "变更类型参数错误",
            'balance.required' => "金额必填",
            'balance.integer'  => "金额类型错误",
            'remark.string'    => "备注为字符",
        ]);
        if ($validator->fails()) {
            return respError($validator->errors()->first());
        }

        $user = $request->get("user");

        // 获取用户余额
        $userBalance = Users::query()->select(['balance'])->where('id', $user['id'])->first();
        // 剩余余额
        $laveBalance = $userBalance['balance'];

        // type 0 减少 1 增加
        $type = $request->input('type');
        // 余额
        $balance = $request->input('balance');
        if ($type == 0) {
            if ($laveBalance < $balance) {
                return respError(sprintf("余额不足:剩余%d", $userBalance['balance']));
            }
            $laveBalance = $laveBalance - $balance;
        } else {
            $laveBalance = $laveBalance + $balance;
        }

        $remark = $request->input('remark');
        $isAdd = $type == 1;
        OperateBalance::dispatch($user['id'], 2, $balance, $isAdd, $remark);

        return respSuccess(['balance' => $laveBalance]);
    }

    /**
     * 用户详情
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        $user = $request->get("user");
        $userModel = Users::query()
            ->select(['id', 'username', 'balance', 'status', 'mac'])
            ->where('id', $user['id'])
            ->first();
        return respSuccess($userModel->toArray());
    }
}
