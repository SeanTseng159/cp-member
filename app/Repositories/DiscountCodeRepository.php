<?php
namespace App\Repositories;

use Illuminate\Database\QueryException;
use App\Models\DiscountCode;

class DiscountCodeRepository
{
    protected $model;

    public function __construct(DiscountCode $discountCode)
    {
        $this->model = $discountCode;
    }

    public function all()
     {
          return $this->model->all();
     }

    public function discountFirst()
    {
        $date = date('Y-m-d H:i:s');
        return $this->model->where('discount_first_type',1)
                            ->where('discount_code_status',1)
                            ->where('discount_code_starttime', '<=', $date)
                            ->where('discount_code_endtime', '>', $date)
                            ->get();
    }
}
