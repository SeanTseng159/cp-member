<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OptPaymentMethod;

class PaymentMethodRepository extends BaseRepository
{

    public function __construct(OptPaymentMethod $model)
    {
        $this->model = $model;
    }

    /**
     * 取全部
     * @return mixed
     */
    public function all($lang)
    {
        return $this->model->where('tag_type', 5)
                            ->where('tag_status', 1)
                            ->orderBy('tag_top', 'desc')
                            ->orderBy('tag_sort', 'asc')
                            ->get();
    }
}
