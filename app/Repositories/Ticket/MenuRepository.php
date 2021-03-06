<?php
/**
 * User: lee
 * Date: 2019/01/31
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;

use App\Models\Ticket\Menu;

class MenuRepository extends BaseRepository
{
    public function __construct(Menu $model)
    {
        $this->model = $model;
    }

    /**
     * 取單一
     * @param  $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->with([
                                'category',
                                'imgs',
                                'prodSpecPrice' => function($query) {
                                    $query->select('prod_spec_price_id', 'prod_spec_id', 'prod_spec_price_stock', 'prod_spec_price_value', 'prod_spec_price_onsale_time', 'prod_spec_price_offsale_time');
                                },
                                'prodSpecPrice.prodSpec' => function($query) {
                                    $query->select('prod_spec_id', 'prod_id');
                                },
                                'prodSpecPrice.prodSpec.productOnShelf' => function($query) {
                                    $query->select('prod_id', 'prod_limit_num');
                                }
                            ])
                            ->where('status', 1)
                            ->find($id);
    }

    /**
     * 取關鍵字找菜單
     * @param  $keyword
     * @return mixed
     */
    public function getDiningCarsByKeyword($keyword = '')
    {
        return $this->model->select('dining_car_id')
                            ->where('status', 1)
                            ->where('name', 'like', '%' . $keyword . '%')
                            ->get();
    }

    /**
     * 取關鍵字找菜單
     * @param  $keyword
     * @return mixed
     */
    public function findByPaidDiningCar($prodSpecPriceId = 0)
    {
        return $this->model->with('diningCar')
                            ->whereHas('diningCar', function($query) {
                                $query->where('level', '>', 0)
                                    ->where('expired_at', '>=', date('Y-m-d H:i:s'));
                            })
                            ->where('prod_spec_price_id', $prodSpecPriceId)
                            ->first();
    }
}
