<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 10:40
 */

namespace Ksd\Mediation\Magento;


use GuzzleHttp\Client as GuzzleHttpClient;
use Ksd\Mediation\Core\Client\BaseClient;
use Ksd\Mediation\Helper\EnvHelper;

class Client extends BaseClient
{
    use EnvHelper;

    protected $userToken;

    public function __construct($defaultAuthorization = true)
    {
        parent::__construct();
        $this->token = $this->env('MAGENTO_ADMIN_TOKEN');
        $this->baseUrl = $this->env('MAGENTO_API_PATH');
        $this->client = new GuzzleHttpClient([
            'base_uri' => $this->baseUrl
        ]);
        if($defaultAuthorization) {
            $this->headers = [
                'Authorization' => 'Bearer ' . $this->token
            ];
        }
    }

    /**
     * 設定 user token
     * @param $token
     * @return $this
     */
    public function userAuthorization($token)
    {
        $this->userToken = $token;
        $this->authorization($token);
        return $this;
    }
}