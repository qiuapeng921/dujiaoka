<?php

namespace App\Http\Controllers\Home;

use App\Models\Goods;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\BaseController;


/**
 * 商品控制器
 */
class GoodsController extends BaseController
{
    /**
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $goodsList = Goods::query()->get()->toArray();
        return respSuccess($goodsList);
    }
}
