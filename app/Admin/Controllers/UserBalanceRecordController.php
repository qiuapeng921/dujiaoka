<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Grid;
use App\Admin\Repositories\UserBalanceRecord;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->column('user_id', '用户id');
            $grid->column('before_balance', '变更前余额');
            $grid->column('after_balance', '变更后余额');
            $grid->column('type')->display(function ($type) {
                if ($type == 1) {
                    return "增加";
                } else {
                    return "减少";
                }
            });
            $grid->column('remark', '备注');
            $grid->column('created_at')->sortable();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->whereBetween('created_at', function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                    $q->where('created_at', '>=', $start)->where('created_at', '<=', $end);
                })->datetime();
                $filter->scope(admin_trans('dujiaoka.trashed'))->onlyTrashed();
            });
        });
    }
}
