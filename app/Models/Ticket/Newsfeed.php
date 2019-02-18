<?php
/**
 * User: lee
 * Date: 2019/01/30
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Newsfeed extends BaseModel
{
	/**
     * 取得封面圖
     */
    public function mainImg()
    {
        return $this->hasOne('App\Models\Ticket\NewsfeedImg')->where('sort', 1);
    }

    /**
     * 取得所有圖片
     */
    public function imgs()
    {
        return $this->hasMany('App\Models\Ticket\NewsfeedImg');
    }
}
