<?php

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\TagEvent;

class TagEventRepository extends BaseRepository
{

    public function __construct(TagEvent $model)
    {
        $this->model = $model;
    }

    /**
     * 取單一分類資料
     * @return mixed
     */
    public function getByTagId($id)
    {
        return $this->model->where('tag_id', $id)
                            ->get();
    }
} 