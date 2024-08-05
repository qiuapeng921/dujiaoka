<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserAuth
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @param Closure $next
     *
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取请求头
        $authorization = $request->header("Authorization", "");
        if (!$authorization) {
            return respError("未授权");
        }

        $userInfo = redisClient()->get($authorization);
        if (!$userInfo) {
            return respError("授权过期");
        }

        $request->attributes->add(['user' => json_decode($userInfo, true)]);

        return $next($request);
    }
}
