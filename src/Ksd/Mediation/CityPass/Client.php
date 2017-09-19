<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 10:40
 */

namespace Ksd\Mediation\CityPass;


use GuzzleHttp\Client as GuzzleHttpClient;
use Ksd\Mediation\Core\Client\BaseClient;
use Ksd\Mediation\Helper\EnvHelper;


class Client extends BaseClient
{
    use EnvHelper;

    public function __construct()
    {
        $this->baseUrl = $this->env('CITY_PASS_API_PATH');
        $this->client = new GuzzleHttpClient([
            'base_uri' => $this->baseUrl
        ]);
    }
}