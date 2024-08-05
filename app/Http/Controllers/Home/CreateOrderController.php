<?php

namespace App\Http\Controllers\Home;

use App\Models\Pay;
use App\Service\PayService;
use App\Service\OrderService;
use Illuminate\Http\JsonResponse;
use App\Service\CreateOrderService;
use App\Exceptions\RuleValidationException;
use App\Http\Controllers\BaseController;
use App\Service\OrderProcessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 订单控制器
 */
class CreateOrderController extends BaseController
{
    /**
     * 订单服务层
     * @var OrderService
     */
    private $orderService;

    /**
     * 订单处理层.
     * @var OrderProcessService
     */
    private $orderProcessService;

    /**
     * 支付服务层
     * @var PayService
     */
    private $payService;

    public function __construct()
    {
        $this->orderService = app('Service\OrderService');
        $this->orderProcessService = app('Service\OrderProcessService');
        $this->payService = app('Service\PayService');
    }

    /**
     * 创建订单
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function createOrder(Request $request): JsonResponse
    {
        $createOrderService = new CreateOrderService($this->orderService, $this->orderProcessService);
        DB::beginTransaction();
        try {
            $orderSn = $createOrderService->createOrder($request);
            DB::commit();
            return respSuccess(['orderSn' => $orderSn]);
        } catch (RuleValidationException $exception) {
            DB::rollBack();
            return respError($exception->getMessage());
        }
    }


    /**
     * 获取支付网关
     * @return JsonResponse
     */
    public function payWays(): JsonResponse
    {
        // 加载支付方式.
        $client = Pay::PAY_CLIENT_PC;
        if (app('Jenssegers\Agent')->isMobile()) {
            $client = Pay::PAY_CLIENT_MOBILE;
        }

        $payWays = $this->payService->pays($client);

        return respSuccess($payWays);
    }

    public function pay(Request $request): JsonResponse
    {
        $pay = $request->input('pay');
        $orderSn = $request->input('order_sn');
        $payUrl = url('pay-gateway', ['handle' => urlencode($pay['pay_handleroute']), 'payway' => $pay['pay_check'], 'orderSN' => $orderSn]);
        return respSuccess(['payUrl' => $payUrl]);
    }
}
