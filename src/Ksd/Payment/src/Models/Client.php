<?php
/**
 * User: Lee
 * Date: 2018/07/10
 * Time: 上午 10:40
 */

namespace Ksd\Payment\Models;

use GuzzleHttp\Client as GuzzleHttpClient;
use Ksd\Payment\Core\BaseClient;
use App\Traits\JWTTokenHelper;

class Client extends BaseClient
{
	use JWTTokenHelper;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = env('PAYMENT_API_PATH');
        $this->client = new GuzzleHttpClient([
            'base_uri' => $this->baseUrl
        ]);

        // 產指定Token
        $token = $this->JWTencode([
        		'iss' => "KSD_PAYMENT",
        		'sub' => env('PAYMENT_API_HASH_IV'),
            	'iat' => time(),
        	], env('PAYMENT_API_HASH_KEY'));

        $this->putHeader('KSD-MerchantId', env('PAYMENT_API_MERCHANT_ID'));
        $this->authorization($token);
    }
}
