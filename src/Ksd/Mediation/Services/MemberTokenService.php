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

    public function __construct(MemberService $memberService, MagentoCustomer $magentoCustomer)
    {
        $this->memberService = $memberService;
        $this->magentoCustomer = $magentoCustomer;
    }

    /**
     * 取得 magento customer token
     * @return string
     */
    public function magentoUserToken()
    {
        $data = $this->JWTdecode();
        if (empty($data)) {
            return '';
        }
        $member = $this->memberService->find($data->id);

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
            'exp' => $tokenData->exp,
            'id' => $tokenData->id,
            'signature' => $signature
        ];

        return $this->JWTencode($data);
    }
}
