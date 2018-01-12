<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 10:40
 */

namespace Ksd\Mediation\Magento;


use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\App;
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
        $baseUrl = sprintf($this->baseUrl, $this->lang());

        $this->client = new GuzzleHttpClient([
            'base_uri' => $baseUrl
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

    /**
     * 取得語系
     * @return mixed
     */
    private function lang()
    {
        if (empty($this->lang)) {
            return App::getLocale();
        }
        return $this->correspondingLang($this->lang);
    }

    /**
     * 語系對應表
     * @param $lang
     * @return mixed
     */
    private function correspondingLang($lang)
    {
        $languages = [
            'zh-TW' => 'zh_hant_tw'
        ];
        if (array_key_exists($lang, $languages)) {
            return $languages[$lang];
        }
        return $lang;
    }
}