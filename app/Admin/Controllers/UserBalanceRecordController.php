<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Repositories\Order;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Pay;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use App\Admin\Repositories\UserBalanceRecord;
use Dcat\Admin\Http\Controllers\AdminController;
use App\Models\Order as OrderModel;

class UserBalanceRecordController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserBalanceRecord(), function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('user_id','用户id');
            $grid->column('before_balance','变更前余额');
            $grid->column('after_balance','变更后余额');
            $grid->column('type');
            $grid->column('remark','备注');
            $grid->column('created_at')->sortable();
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('username');
                $filter->whereBetween('created_at', function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                    $q->where('created_at', '>=', $start)
                        ->where('created_at', '<=', $end);
                })->datetime();
                $filter->scope(admin_trans('dujiaoka.trashed'))->onlyTrashed();
            });
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Order(['goods', 'coupon', 'pay']), function (Form $form) {
            $form->display('id');
            $form->display('order_sn');
            $form->text('title');
            $form->display('goods.gd_name', admin_trans('order.fields.goods_id'));
            $form->display('goods_price');
            $form->display('buy_amount');
            $form->display('coupon.coupon', admin_trans('order.fields.coupon_id'));
            $form->display('coupon_discount_price');
            $form->display('wholesale_discount_price');
            $form->display('total_price');
            $form->display('actual_price');
            $form->display('email');
            $form->textarea('info');
            $form->display('buy_ip');
            $form->display('pay.pay_name', admin_trans('order.fields.pay_id'));
            $form->radio('status')->options(OrderModel::getStatusMap());
            $form->text('search_pwd');
            $form->display('trade_no');
            $form->radio('type')->options(OrderModel::getTypeMap());
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
