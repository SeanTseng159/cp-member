<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:02
 */

namespace Ksd\Mediation\Repositories;


use Carbon\Carbon;
use Ksd\Mediation\Config\ProjectConfig;


use Ksd\Mediation\CityPass\CartMore as CityPassCart;

use Ksd\Mediation\Services\MemberTokenService;
use  App\Models\CartItems;
use App\Models\Carts;


class CartMoreRepository extends BaseRepository
{
    const INFO_KEY = 'cart:user:info:%s:%s';
    const INFO_KEY_M = 'cart:user:info_magento:%s:%s';
    const INFO_KEY_C = 'cart:user:info_ct_pass:%s:%s';
    const DETAIL_KEY= 'cart:user:detail:%s:%s';
    const DETAIL_KEY_M = 'cart:user:detail_magento:%s:%s';
    const DETAIL_KEY_C= 'cart:user:detail_ct_pass:%s:%s';

    private $memberTokenService;
    private $cartItemsModel;
    private $result = false;

    private $magentoInfo = [];
    private $cityPassInfo = [];
    private $magentoDetail = [];
    private $cityPassDetail = [];

    public function __construct(MemberTokenService $memberTokenService)
    {

        $this->cityPass = new CityPassCart();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
        $this->carts = new Carts();
        $this->cartItemsModel=new CartItems();
        $this->setMemberId($this->memberTokenService->getId());
    }

    /**
     * 取得購物車簡易資訊
     * @return mixed
     */
    public function info($cartNumber)
    {        
        return $this->cityPassInfo = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info($cartNumber);
    }

    /**
     * 取得購物車資訊(依來源)
     * @param $parameter
     * @return mixed
     */
    public function mine($cartNumber)
    {
        
        
        return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->detail($cartNumber);
        
    }





    /**
     * 取得個人有多少台購物車
     * @param $parameter
     * @return mixed
     */
    public function getCartByMemberId($memberId)
    {

        return $this->cartItemsModel->where('member_id', $memberId)->orderBy('cart_item_type')->get();
    }


    /**
     * 商品加入購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        foreach ($parameters->cityPass() as $item) {
            $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->add($item);
        }
        
        
        return $this->result;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {

        foreach ($parameters->cityPass() as $item) {
            $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->update($item);
        }

        return $this->result;
    }



    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
       
        foreach ($parameters->cityPass() as $item) {
            $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->delete($item);
        }


        return $this->result;
    }


    /**
     * 更新購物車過期時間
     * @param $parameters
     * @return bool
     */
    public function updateExpiredDate($parameters)
    {
        $member_id = $this->memberTokenService->getId();
        $filter_params['member_id'] = $member_id;
        $update_params = [
            'last_notified_at' => Carbon::now(),
            'began_at' => Carbon::now(),
        ];

        if(!empty($parameters->cityPass())) {
            $filter_params['type'] = ProjectConfig::CITY_PASS;
        }
        try {
            Carts::updateOrCreate($filter_params, $update_params);
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }



}
