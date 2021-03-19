<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 5:26
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Magento\SalesRule as MagentoSalesRule;
use Ksd\Mediation\CityPass\SalesRule as CityPassSalesRule;
use Ksd\Mediation\Services\MemberTokenService;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\OrderDiscount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesRuleRepository extends BaseRepository
{
    use EnvHelper;

    private $memberTokenService;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoSalesRule();
        $this->cityPass = new CityPassSalesRule();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
        $this->coupon_model = new Coupon;
        $this->order_discount_model = new OrderDiscount;
        $this->orders_model = new Orders;
    }

    /**
     * 使用折扣優惠
     * @param $parameters
     * @return bool
     */
    public function addCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            if($this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->addCoupon($parameters->code)) {
                return $this->magento->authorization($this->env('MAGENTO_ADMIN_TOKEN'))->couponDetail($parameters->code);
            }
        } else if ($parameters->source === ProjectConfig::CITY_PASS or $parameters->source === ProjectConfig::CITY_PASS_PHYSICAL) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->addCoupon($parameters);

        }
    }

    /**
     * 取消折扣優惠
     * @param $parameters
     * @return bool
     */
    public function deleteCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->deleteCoupon();
        } else if ($parameters->source === ProjectConfig::CITY_PASS or $parameters->source === ProjectConfig::CITY_PASS_PHYSICAL) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->deleteCoupon($parameters);
        }
    }


    /** 
    * 使用"店家新增之優惠券"的折扣優惠(此Repository中有"CouponOnline"的function都是，方便區別)
    * 此部分與addCounpon差別在於，addCoupon為使用"站台發送之優惠券"，此功能較龐大，與DB溝通也須經過CI專案進行邏輯處理
    * 而此api為後來開發的"店家新增之優惠券"功能，此部分功能會直接透過model撈資料，而不像之前通過CI專案，兩API差別會由此部分開始
    * @param $coupon_online_code 優惠券代碼
    * @return array
    */
    public function getCouponOnlineDataByCode($coupon_online_code)//透過優惠券代碼查優惠券資料
    {
        //$coupon_data = $this->coupon_model->where('online_code_value', $coupon_online_code)->find(1);
        $coupon_data = $this->coupon_model->where('online_code_value',$coupon_online_code)->first();
        return $coupon_data;
    }

    //檢查此張優惠券是否可使用(期限、總使用次數、每人使用次數)
    public function checkCouponOnlineCanUsed($coupon_data,$memberID)
    {
        $canUsed = true;//此變數代表此優惠券能否使用
        $error_message = "";

        //判斷是否逾期
        if (($coupon_data->start_at > Carbon::now()) || ($coupon_data->expire_at < Carbon::now())) {
            $canUsed = false;
            $error_message .= "此優惠券不在使用期限內";
        }
        //判斷總使用次數是否超過
        $coupon_be_used_count = $this->order_discount_model
        ->where('discount_type','4')//4表示為店家開立的線上優惠券
        ->where('discount_id',$coupon_data->id)
        ->count();//查詢這張優惠券曾被使用過幾次

        if($coupon_be_used_count >= $coupon_data->qty)//若已被使用次數>=可使用次數，
        {
            $canUsed = false;
            $error_message .= "此優惠券已達總使用上限次數";
        }

        //判斷個人使用次數是否超過個人上限
        //此部分開發時ORM會有ERROR，先改用RAW SQL下法，待後續維護有時間可改回ORM
        //raw sql找出來的值會直接做成一個object回到$sql_result內
        $sql = ' order_discounts.discount_id ='.$coupon_data->id.' AND order_discounts.discount_type=4 AND orders.member_id ='.$memberID.' ';
        $sql_result = DB::connection('backend')->select('SELECT 
        COUNT(*) AS "member_used_coupon_count"
        FROM order_discounts
        INNER JOIN orders ON orders.order_no = order_discounts.order_no
        WHERE '.$sql.'
        ');

        $counpon_be_used_by_member = $sql_result[0]->member_used_coupon_count;//此值表示這個使用者已經再之前訂單使用過幾次此優惠券

        if($counpon_be_used_by_member >= $coupon_data->limit_qty)
        {
            $canUsed = false;
            $error_message = "此優惠券已達每名會員可使用上限次數";
        }

        $CanUsedResult = array("canUsed"=>$canUsed,"error_message"=>$error_message);//將可否使用 and 不可使用之理由包成array

        return $CanUsedResult;


    }

}