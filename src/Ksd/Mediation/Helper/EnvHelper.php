<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 11:46
 */

namespace Ksd\Mediation\Helper;


trait EnvHelper
{
    /**
     * 設定所有環境變數
     */
    private function all()
    {
        $root = empty($_ENV['FCPATH']) ? './' : FCPATH ;
        $path = $root . ".env";
        if (file_exists($path)) {
            $envFile = fopen($root . ".env", "r");
            while(!feof($envFile)) {
                $env = trim(fgets($envFile));
                if (!empty($env)) {
                    putenv($env);
                }
            }
            fclose($envFile);
        }
    }

    /**
     * 取得環境變數
     * @param $key
     * @param string $default
     * @return array|false|mixed|string
     */
    public function env($key, $default = '')
    {
        if(function_exists('env')) {
            return empty(env($key)) ? $default : env($key);
        }
        $this->all();
        return empty(getenv($key)) ? $default : getenv($key);
    }
}