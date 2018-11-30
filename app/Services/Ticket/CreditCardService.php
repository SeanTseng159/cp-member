<?php
/**
 * User: lee
 * Date: 2018/11/30
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use Ksd\Mediation\CityPass\Checkout;
use App\Traits\JWTTokenHelper;

class CreditCardService extends BaseService
{
    use JWTTokenHelper;

    public function __construct()
    {
        $this->repository = new Checkout;
    }

    /**
     * 信用卡送金流(台新)
     * @param $parameters
     * @return array|mixed
     */
    public function transmit($memberId, $parameters)
    {
        return $this->repository->authorization($this->generateToken())
                                ->transmit($memberId, $parameters);
    }

    /**
     * 建立 token for citypass金流
     * @return string
     */
    private function generateToken()
    {
        $token = [
            'exp' => time() + 120,
            'secret' => 'a2f8b3503c2d66ea'
        ];

        return $this->JWTencode($token);
    }
}
