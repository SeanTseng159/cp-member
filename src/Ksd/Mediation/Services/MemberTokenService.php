<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/18
 * Time: 上午 11:30
 */

namespace Ksd\Mediation\Services;


use App\Services\MemberService;
use App\Traits\JWTTokenHelper;
use Ksd\Mediation\Magento\Customer as MagentoCustomer;
use Request;

class MemberTokenService
{
    use JWTTokenHelper;

    private $memberService;
    private $magentoCustomer;
    private $jwtData;

    public function __construct(MemberService $memberService, MagentoCustomer $magentoCustomer)
    {
        $this->memberService = $memberService;
        $this->magentoCustomer = $magentoCustomer;

        $this->jwtData = $this->getJwtData();
    }

    /**
     * 取得 Jwt Data
     * @return string
     */
    private function getJwtData()
    {
        $data = $this->JWTdecode();
        return $data ?: null;
    }

    /**
     * 取得 magento customer token
     * @return string
     */
    public function magentoUserToken()
    {
        if (!$this->jwtData) return '';
        
        $member = $this->memberService->find($this->jwtData->id);
        return $this->magentoCustomer->token($member);
    }

    /**
     * city pass 直接轉拋 token
     * @return mixed
     */
    public function cityPassUserToken()
    {
        return Request::bearerToken();
    }

    /**
     * city pass 直接轉拋 token
     * @return mixed
     */
    public function cityPassUserTokenForIpasspay($token, $order_id)
    {
        if (!$token) return '';

        $tokenData = $this->JWTdecode($token);

        $exp = time() + 600;
        $signature = $order_id . '_' . $exp;

        $data = [
            'iss' => $tokenData->iss,
            'iat' => $tokenData->iat,
            'exp' => $exp,
            'id' => $tokenData->id,
            'signature' => $signature
        ];

        return $this->JWTencode($data);
    }

    /**
     * city pass 直接轉拋 token
     * @return mixed
     */
    public function cityPassUserTokenForIpasspayByMemberId($member_id, $order_id)
    {
        if (!$member_id) return '';

        $iat = time();
        $exp = time() + 600;
        $signature = $order_id . '_' . $exp;

        $data = [
            'iss' => env('JWT_ISS', 'CityPass'),
            'iat' => $iat,
            'exp' => $exp,
            'id' => $member_id,
            'signature' => $signature
        ];

        return $this->JWTencode($data);
    }

    /**
     * 取得 member id
     * @return string
     */
    public function getId()
    {
        return ($this->jwtData) ? $this->jwtData->id : 0;
    }

    /**
     * 取得 member email for magento order
     * @return string
     */
    public function getEmail()
    {
        if (!$this->jwtData) return null;

        $member = $this->memberService->find($this->jwtData->id);

        if(isset($member)) {
            if($member->openPlateform !== 'citypass') {
                return $member->openPlateform . '_' . $member->openId;
            }else{
                return $member->email;
            }
        }else{
            return null;
        }
    }
}
