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
     * 可以銷售
    */
    public function scopeOnSale($query)
    {
        $date = date('Y-m-d H:i:s');

        return $query->notDeleted()
                    ->where('prod_onshelf', 1)
                    ->whereIn('prod_type', [1, 2])
                    ->where('prod_onshelf_time', '<=', $date)
                    ->where('prod_offshelf_time', '>=', $date)
                    ->where('prod_onsale_time', '<=', $date)
                    ->where('prod_offsale_time', '>=', $date);
    }

    /**
     * 再架上
    */
    public function scopeOnShelf($query)
    {
        $date = date('Y-m-d H:i:s');

        return $query->notDeleted()
                    ->where('prod_onshelf', 1)
                    ->whereIn('prod_type', [1, 2])
                    ->where('prod_onshelf_time', '<=', $date)
                    ->where('prod_offshelf_time', '>=', $date);
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

    /**
     * 取得運費
     */
    public function shippingFees()
    {
        return $this->hasMany('App\Models\Ticket\ShippingFee', 'prod_id')->orderBy('lower', 'asc');
    }

    /**
     * 取得組合子商品
     */
    public function groups()
    {
        return $this->hasMany('App\Models\Ticket\ProductGroup', 'prod_id')->notDeleted()->orderBy('prod_group_sort', 'asc');
    }

    /**
     * 取得供應商
     */
    public function supplier()
    {
        return $this->hasOne('App\Models\Ticket\Supplier', 'supplier_id','supplier_id');
    }
}
