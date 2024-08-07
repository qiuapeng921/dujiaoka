<?php

namespace App\Admin\Repositories;

use App\Models\UserBalanceRecord as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserBalanceRecord extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
