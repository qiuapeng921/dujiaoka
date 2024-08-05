<?php

namespace App\Http\Controllers\Home;

use App\Models\Users;
use App\Jobs\OperateBalance;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RuleValidationException;
use Illuminate\Http\Request;

/**
 * 授权控制器
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'mac'      => 'required',
        ], [
            'username.required' => "账号必填",
            'password.integer'  => "密码必填",
            'mac.required'      => "mac地址必填",

        ]);
        if ($validator->fails()) {
            return respError($validator->errors()->first());
        }

        $username = $request->input("username");

        // 判断用户是否存在
        $user = Users::query()
            ->select(['id', 'username', 'balance', 'status', 'mac'])
            ->where('username', $username)
            ->first();
        if (!$user) {
            return respError("用户不存在");
        }

        $mac = $request->input("mac");
        // 判断机器码是否一致
        if ($user['mac'] && $user['mac'] != $mac) {
            return respError("机器码不一致,请联系购买方解除");
        }

        Users::query()->where('id', $user['id'])->update(['mac' => $mac]);

        $token = md5($user['id']);
        redisClient()->set($token, json_encode($user));

        return respSuccess(['user' => $user, 'token' => $token]);
    }

    /**
     * 用户注册
     *
     * @param Request $request
     *
     * @return JsonResponse|void
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => "账号必填",
            'password.integer'  => "密码必填",
        ]);
        if ($validator->fails()) {
            return respError($validator->errors()->first());
        }

        $username = $request->input("username");
        $password = md5($request->input("password"));

        // 判断用户是否存在
        $user = Users::query()->where("username", $username)->exists();
        if ($user) {
            return respError("用户已存在", 10001);
        }

        $userId = Users::query()->insertGetId([
            "username" => $username,
            "password" => $password,
        ]);

        OperateBalance::dispatch($userId, 1);

        return respSuccess();
    }
}
