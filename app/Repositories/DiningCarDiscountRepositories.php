<?php
/**
 * Created by Fish.
 * 2019/12/19 5:50 下午
 */

namespace App\Repositories;

use App\Models\DiningCarDiscount;
use Carbon\Carbon;


class DiningCarDiscountRepositories extends BaseRepository
{
    protected $model;

    public function __construct(DiningCarDiscount $model)
    {
        $this->model = $model;
    }

    public function find($id, $memberId)
    {
        $now = Carbon::now();

        return $this->model
            ->with('image')
            ->where('id', $id)
            ->where('start_at','<=', $now)
            ->where('end_at', '>',$now)
            ->first();
    }
}