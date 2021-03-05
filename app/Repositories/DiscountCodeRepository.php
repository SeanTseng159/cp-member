<?php
namespace App\Repositories;

use Illuminate\Database\QueryException;
use App\Models\Ticket\DiscountCode;
use App\Models\Ticket\DiscountCodeBlockProd;

class DiscountCodeRepository
{
    protected $model;

    public function __construct(
        DiscountCode $discountCode,
        DiscountCodeBlockProd $discountCodeBlockProd
    )
    {
        $this->model = $discountCode;
        $this->discountCodeBlockProd = $discountCodeBlockProd;
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
                            ->whereColumn('discount_code_limit_count','>','discount_code_used_count')
                            ->get();
    }

    public function getEnableDiscountCode($code)
    {
        $date = date('Y-m-d H:i:s');
        return $this->model
            ->where('discount_code_value', $code)
            ->where('discount_code_status',1)
            ->where('discount_code_starttime', '<=', $date)
            ->where('discount_code_endtime', '>', $date)
            ->whereColumn('discount_code_limit_count','>','discount_code_used_count')
            ->first();
    }

    public function allEnableDiscountByProd($prodId)
    {
        $date = date('Y-m-d H:i:s');

        $discountCode = $this->model
                    ->whereHas('discountCodeTag.tagProdId',
                        function ($query) use ($prodId) {
                            
                            $query->where('prod_id', $prodId);
                                
                        })
                    ->where('discount_code_status',1)
                    ->where('discount_code_starttime', '<=', $date)
                    ->where('discount_code_endtime', '>', $date)
                    ->whereColumn('discount_code_limit_count','>','discount_code_used_count')
                    ->get();
                    
        $discountCodeArr = [];
        foreach ($discountCode as $key => $value) {
            $isBlockProd = $this->discountCodeBlockProd
                ->where('prod_id',$prodId)
                ->where('discount_code_id',$value->discount_code_id)
                ->where('deleted_at',0)
                ->exists();
            if(!$isBlockProd){
                $discountCodeArr[] = $value;
            }
        }
        
        return $discountCodeArr;
    }
    
    public function isInvalidDiscountCode($code)
    {
        $date = date('Y-m-d H:i:s');

        $discountCode = $this->model
                        ->where('discount_code_value', $code)
                        // ->where('discount_code_status',1)
                        // ->where('discount_code_starttime', '<=', $date)
                        // ->where('discount_code_endtime', '>', $date)
                        // ->where('discount_code_member_use_count', '!=', 1)
                        // ->whereColumn('discount_code_limit_count','>=','discount_code_used_count')
                        ->first();

        return $discountCode;
    }
}
