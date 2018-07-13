<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:02
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Config\CacheConfig;
use Ksd\Mediation\Magento\Cart as MagentoCart;
use Ksd\Mediation\CityPass\Cart as CityPassCart;

use Ksd\Mediation\Services\MemberTokenService;
use App\Models\Carts;
use Illuminate\Support\Carbon;

class CartRepository extends BaseRepository
{
    const INFO_KEY = 'cart:user:info:%s:%s';
    const INFO_KEY_M = 'cart:user:info_magento:%s:%s';
    const INFO_KEY_C = 'cart:user:info_ct_pass:%s:%s';
    const DETAIL_KEY= 'cart:user:detail:%s:%s';
    const DETAIL_KEY_M = 'cart:user:detail_magento:%s:%s';
    const DETAIL_KEY_C= 'cart:user:detail_ct_pass:%s:%s';

    private $memberTokenService;
    private $result = false;

    private $magentoInfo = [];
    private $cityPassInfo = [];
    private $magentoDetail = [];
    private $cityPassDetail = [];

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoCart();
        $this->cityPass = new CityPassCart();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
        $this->carts = new Carts();
        $this->setMemberId($this->memberTokenService->getId());
    }

    /**
     * 取得購物車簡易資訊
     * @return mixed
     */
    public function info()
    {
        $this->magentoInfo = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info();
        $this->cityPassInfo = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info();

            return [
                ProjectConfig::MAGENTO => $this->magentoInfo,
                ProjectConfig::CITY_PASS => $this->cityPassInfo
            ];

    }

    /**
     * 取得購物車資訊
     * @return mixed
     */
    public function detail()
    {
        $this->magentoDetail  = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->detail();
        $this->cityPassDetail = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->detail();

        return [
            ProjectConfig::MAGENTO => $this->magentoDetail,
            ProjectConfig::CITY_PASS => $this->cityPassDetail
        ];
    }


    /**
     * 取得購物車資訊(依來源)
     * @param $parameter
     * @return mixed
     */
    public function mine($parameter, $token = null)
    {
        $source = $parameter->source;
        if($source === ProjectConfig::MAGENTO) {
<<<<<<< HEAD
            $token = empty($token) ? $this->memberTokenService->magentoUserToken() : $token;
            return $this->magento->userAuthorization($token)->detail(true, $token);
=======
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->detail();
>>>>>>> dev_magento_change_buy
        }else if($source === ProjectConfig::CITY_PASS) {
            $token = empty($token) ? $this->memberTokenService->cityPassUserToken() : $token;
            return $this->cityPass->authorization($token)->detail();
        }else{
            return "nodata";
        }

    }

    /**
     * 取得一次性購物車資訊並加入購物車(依來源)
     * @param $parameter
     * @return mixed
     */
    public function oneOff($parameter)
    {
        $source = $parameter->source;
        if($source === ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->getOneOffCart($this->memberId);
        } else if ($source === ProjectConfig::CITY_PASS) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->detail();
        } else {
            return "nodata";
        }
    }

    /**
     * 商品加入購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {

        if (!empty($parameters->magento())) {
            $this->result = $this->magento->addCacheCart($this->memberId, $parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->add($item);
            }
        }
        // $this->cleanCache();

        return $this->result;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->update($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->update($item);
            }
        }
        // $this->cleanCache();

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
        if ( ! empty($parameters->magento()) ) {
            $filter_params['type'] = ProjectConfig::MAGENTO;
        } else if(!empty($parameters->cityPass())) {
            $filter_params['type'] = ProjectConfig::CITY_PASS;
        }
        try {
            Carts::updateOrCreate($filter_params, $update_params);
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->delete($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->delete($item);
            }

        }
        //$this->cleanCache();

        return $this->result;
    }

    /**
     * 刪除購物車內指定商品
     * @param string $source
     * @param array $itemIds
     * @param string $token
     * @return mixed
     */
    public function deleteByItemIds($source, $memberId, $itemIds)
    {
        $token = $this->memberTokenService->getUserTokenByMemberId($source, $memberId);
        if($source === ProjectConfig::MAGENTO) {
            foreach ($itemIds as $id) {
                $items[] = ['id' => $id];
            }
            $this->result = $this->magento->userAuthorization($token)->delete($items);
        }else if($source === ProjectConfig::CITY_PASS) {
            foreach ($itemIds as $id) {
                $item = ['id' => $id];
                $this->result = $this->cityPass->authorization($token)->delete($item);
            }
        }
        $this->cleanCache();

        return $this->result;
    }

    /**
     * 刪除過期購物車紀錄
     * @param string $source
     * @param int $memberId
     * @return bool
     */
    public function deleteExpiredRecord($source, $memberId)
    {
        return $this->carts->where('type', $source)
                ->where('member_id', $memberId)
                ->delete();
    }

    /**
     * 清除快取
     */
    public function cleanCache()
    {
        $this->cacheKey(self::INFO_KEY);
        $this->cacheKey(self::DETAIL_KEY);
    }

    /**
     * 清除快取_magento
     */
    public function cleanCacheMagento()
    {
        $this->cacheKey(self::INFO_KEY_M);
        $this->cacheKey(self::DETAIL_KEY_M);
    }


    /**
     * 清除快取_citypass
     */
    public function cleanCacheCityPass()
    {
        $this->cacheKey(self::INFO_KEY_C);
        $this->cacheKey(self::DETAIL_KEY_C);
    }

    /**
     * 取得過期購物車會員 id
     * @param string $source
     * @param int $expire_days 過期天數
     * @return array
     */
    public function expiredCartMemberIds($source, $expire_days)
    {
        return $this->carts->whereRaw('began_at <= DATE_ADD(NOW(), INTERVAL -' . $expire_days . ' DAY)')
                ->where('type', $source)
                ->pluck('member_id')
                ->toArray();
    }


    /**
     * 根據 key 清除快取
     * @param $key
     */
    private function cacheKey($key)
    {
        $this->redis->delete($this->genCacheKey($key));
    }

    /**
     * 建立快取 key
     * @param $key
     * @return string
     */
    private function genCacheKey($key)
    {
        $date = new \DateTime();
        $this->token = $this->memberTokenService->cityPassUserToken();
        return sprintf($key, $this->token,$date->format('Ymd'));
    }
}
