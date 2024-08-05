<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */

namespace App\Service;


use Illuminate\Support\Facades\DB;
use App\Exceptions\RuleValidationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CreateOrderService
{
    /**
     * 订单服务层
     * @var \App\Service\OrderService
     */
    private $orderService;

    /**
     * 订单处理层.
     * @var OrderProcessService
     */
    private $orderProcessService;

    public function __construct(OrderService $orderService, OrderProcessService $orderProcessService)
    {
        $this->orderService = $orderService;
        $this->orderProcessService = $orderProcessService;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws RuleValidationException
     * @throws ValidationException
     */
    public function createOrder(Request $request)
    {
        $this->orderService->validatorCreateOrder($request);
        $goods = $this->orderService->validatorGoods($request);
        $this->orderService->validatorLoopCarmis($request);
        // 设置商品
        $this->orderProcessService->setGoods($goods);
        // 优惠码
        $coupon = $this->orderService->validatorCoupon($request);
        // 设置优惠码
        $this->orderProcessService->setCoupon($coupon);
        $otherIpt = $this->orderService->validatorChargeInput($goods, $request);
        $this->orderProcessService->setOtherIpt($otherIpt);
        // 数量
        $this->orderProcessService->setBuyAmount($request->input('by_amount'));
        // 支付方式
        $this->orderProcessService->setPayID($request->input('payway'));
        // 下单邮箱
        $this->orderProcessService->setEmail($request->input('email'));
        // ip地址
        $this->orderProcessService->setBuyIP($request->getClientIp());
        // 查询密码
        $this->orderProcessService->setSearchPwd($request->input('search_pwd', ''));
        // 创建订单
        $order = $this->orderProcessService->createOrder();
        return $order->order_sn;
    }
}
