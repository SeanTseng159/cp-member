<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Ksd\Mediation\Config\ProjectConfig;

class Product extends BaseModel
{
	protected $table = 'prods';
	protected $primaryKey = 'prod_id';

	protected $appends = ['source', 'full_address'];

	/**
     * 加入來源
     */
	public function getSourceAttribute($value)
    {
        if ($this->is_physical) {
            return ProjectConfig::CITY_PASS_PHYSICAL;
        } else {
            return ProjectConfig::CITY_PASS;
        }
    }

    public function getFullAddressAttribute()
    {
        return $this->prod_county . $this->prod_district . $this->prod_address;
    }

	/**
     * 取得商品所有圖片
     */
  	public function imgs()
    {
        return $this->hasMany('App\Models\Ticket\ProductImg', 'prod_id');
	}

    /**
     * 取得商品封面圖片
     */
    public function img()
    {
        return $this->hasOne('App\Models\Ticket\ProductImg', 'prod_id')->orderBy('img_sort');
    }

    /**
     * 取得規格
     */
    public function specs()
    {
        return $this->hasMany('App\Models\Ticket\ProductSpec', 'prod_id');
    }

    /**
     * 取得關鍵字
     */
    public function keywords()
    {
        return $this->belongsToMany('App\Models\Ticket\Keyword', 'prod_keywords', 'prod_id', 'keyword_id');
    }
        
    public function product_tags()
    {
        return $this->hasMany('App\Models\Ticket\ProductTag', 'prod_id');
    }
}
