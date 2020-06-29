<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;
use Carbon\Carbon;
class DiscountCode extends BaseModel
{

    protected $table = 'discount_codes';

    protected $primaryKey = 'discount_code_id';

    public function discountCodeTags()
    {
        return $this->hasMany(DiscountCodeTag::class, 'discount_code_tag_id', 'discount_code_tag_id');
    }

    /**
     * 取得優惠倦接受的商品
     */
    public function discountCodeTag()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeTag', 'discount_code_id', 'discount_code_id')->where('deleted_at',0);
    }

    /**
     * 取得優惠倦拒絕的商品
     */
    public function discountCodeBlock()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeBlockProd', 'discount_code_id', 'discount_code_id');
    }

    /**
     * 取得優惠倦會員使用情況
     */
    public function discountCodeMember()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeMember', 'discount_code_id', 'discount_code_id');
    }


    
    public function scopeAllow($query)
    {
        return $query->where('discount_code_status', 1)
                    ->where('deleted_at',0)
                    ->where('discount_code_starttime','<=',Carbon::today())
                    ->where('discount_code_endtime','>',Carbon::today());
    }
}
