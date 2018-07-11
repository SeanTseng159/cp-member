<?php
/**
 * User: Lee
 * Date: 2018/05/16
 * Time: 上午 10:40
 */

namespace Ksd\Payment\Core;

use GuzzleHttp\Client as GuzzleHttpClient;

class BaseClient
{
    protected $token;
    protected $baseUrl;
    protected $client;
    protected $headers;
    protected $query;
    protected $parameters;
    protected $json = true;

    public function __construct()
    {
        $this->client = new GuzzleHttpClient();
    }

    /**
     * 清除參數
     */
    public function clear()
    {
        $this->headers = [];
        $this->query = [];
        $this->parameters = [];
    }

    /**
     * 設定 Authorization 金鑰
     * @param $token
     * @return $this
     */
    public function authorization($token)
    {
        $this->putHeader('Authorization', 'Bearer ' . $token);
        return $this;
    }

    /**
     * 設置 header
     * @param $key
     * @param $value
     * @return $this
     */
    protected function putHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * 設置 headers 參數
     * @param array $parameters
     * @return $this
     */
    protected function putHeaders($parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->putHeader($key, $value);
        }
        return $this;
    }

    /**
     * 設置 query 參數
     * @param $key
     * @param $value
     * @return $this
     */
    protected function putQuery($key, $value)
    {
        if (empty($this->query)) {
            $this->query = [];
        }
        $this->query[$key] = $value;
        return $this;
    }

    /**
     * 設置 queries 參數
     * @param array $parameters
     * @return $this
     */
    protected function putQueries($parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->putQuery($key, $value);
        }
        return $this;
    }

    /**
     * 設置參數
     * @param $key
     * @param $value
     * @return $this
     */
    protected function putParameter($key, $value)
    {
        if (empty($this->parameters)) {
            $this->parameters = [];
        }
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * 設置多筆參數
     * @param array $parameters
     * @return $this
     */
    protected function putParameters($parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->putParameter($key, $value);
        }
        return $this;
    }

    /**
     * set Json
     */
    public function setJson($bool = true)
    {
        $this->json = $bool;
        return $this;
    }

    /**
     * 建置傳送設定
     * @return array
     */
    protected function buildOption()
    {
        $option = [];
        if (!empty($this->headers)) {
            $option['headers'] = $this->headers;
        }

        if (!empty($this->query)) {
            $option['query'] = $this->query;
        }

        if (!empty($this->parameters)) {
            if ($this->json) {
                $option['json'] = $this->parameters;
            } else {
                $option['form_params'] = $this->parameters;
            }
        }

        return $option;
    }

    /**
     * 發送 request
     * @param $method
     * @param $path
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function request($method, $path)
    {
        //var_dump($this->buildOption());
        return $this->client->request($method, $path, $this->buildOption());
    }
}
