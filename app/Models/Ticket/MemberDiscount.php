<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class MemberDiscount extends BaseModel
{

    
    protected $table = 'member_discount';
    protected $fillable = ['discount_code_id','member_id','used','status'];

	/**
     * 取得優惠倦
     */
    public function discountCodeAllow()
    {
        return $this->hasOne('App\Models\Ticket\DiscountCode', 'discount_code_id', 'discount_code_id')->allow();
    }

    public function discountCode()
    {
        return $this->hasOne('App\Models\Ticket\DiscountCode', 'discount_code_id', 'discount_code_id');
    }



    /**
     * 取得優惠倦拒絕的商品
     */
    public function discountCodeBlock()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeBlockProd', 'discount_code_id', 'discount_code_id');
    }



    /**
     * 取得優惠倦接受的商品
     */
    public function discountCodeTag()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeTag', 'discount_code_id', 'discount_code_id');
    }


    /**
     * 取得優惠倦會員使用情況
     */
    public function discountCodeMember()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeMember', 'discount_code_id', 'discount_code_id');
    }

    /**
     * 取得優惠倦會員使用情況
     */
    public function discountCodeMemberByMember()
    {
        return $this->hasMany('App\Models\Ticket\DiscountCodeMember', ['member_id','discount_code_id'], ['member_id','discount_code_id']);
    }
    
    
}
 