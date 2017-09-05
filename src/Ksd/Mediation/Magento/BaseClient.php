<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 10:40
 */

namespace Ksd\Mediation\Magento;


use GuzzleHttp\Client;
use Ksd\Mediation\Helper\EnvHelper;

class BaseClient
{
    use EnvHelper;

    protected $token;
    protected $baseUrl;
    protected $client;
    protected $headers;
    protected $query;
    protected $parameters;
    protected $json = true;

    public function __construct()
    {
        $this->token = $this->env('MAGENTO_ADMIN_TOKEN');
        $this->baseUrl = $this->env('MAGENTO_API_PATH');
        $this->client = new Client([
            'base_uri' => $this->baseUrl
        ]);
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token
        ];

    }

    public function authorization($token)
    {
        $this->putHeader('Authorization', 'Bearer ' . $token);
        return $this;
    }

    protected function putHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    protected function putQuery($key, $value)
    {
        if (empty($this->query)) {
            $this->query = [];
        }
        $this->query[$key] = $value;
        return $this;
    }

    protected function putParameter($key, $value)
    {
        if (empty($this->parameters)) {
            $this->parameters = [];
        }
        $this->parameters[$key] = $value;
        return $this;
    }

    protected function putParameters($parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->putParameter($key, $value);
        }
        return $this;
    }

    protected function buildOption()
    {
        $option = [];
        $option['headers'] = $this->headers;

        if (!empty($this->query)) {
            $option['query'] = $this->query;
        }

        if (!empty($this->parameters) && $this->json) {
            $option['json'] = $this->parameters;
        } else {
            $option['body'] = $this->parameters;
        }

        return $option;
    }

    protected function request($method, $path)
    {
        return $this->client->request($method, $path, $this->buildOption());
    }
}