<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/24
 * Time: 上午 09:19
 */

namespace Ksd\Mediation\CityPass;

use Ksd\Mediation\Result\WishlistResult;
use Ksd\Mediation\Helper\EnvHelper;

class Wishlist extends Client
{
    use EnvHelper;

    /**
     * 取得所有收藏列表
     * @return array
     */
    public function items()
    {

        $path = 'wishlist/items';

        $response = $this->request('GET', $path);
        $result = json_decode($response->getBody(), true);
        $data = $result['data'];

        if($result['statusCode'] === 200) {
            if (!isset($data[0])) {
                return [];
            } else {
                return $data;
            }
        }else{
            return [];
        }
    }

    /**
     * 根據商品id 增加商品至收藏清單
     * @param $parameter
     * @return  bool
     */
    public function add($parameter)
    {
        $url = 'wishlist/add/';
        $response = $this->putParameters($parameter)->request('POST', $url);
        $result = json_decode($response->getBody(), true);
        return $result['statusCode'] === 201 ? true : false;
    }

    /**
     * 根據商品id 刪除收藏清單商品
     * @param $parameter
     *  @return  bool
     */
    public function delete($parameter)
    {
        $response = $this->putParameters($parameter)
            ->request('POST', 'wishlist/delete');
        $result = json_decode($response->getBody(), true);

        return $result['statusCode'] === 203 ? true : false;
    }
}