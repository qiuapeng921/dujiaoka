<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class UserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(), function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('id')->sortable();
            $grid->column('username');
            $grid->column('balance','余额')->editable();
            $grid->column('mac','mac地址')->editable();
            $grid->column('status','启用')->switch();
            $grid->column('created_at')->sortable();
            $grid->column('updated_at')->sortable();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('username');
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
