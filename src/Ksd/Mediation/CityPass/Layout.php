<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/30
 * Time: 下午 03:08
 */

namespace Ksd\Mediation\CityPass;

use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\LayoutResult;

class Layout extends Client
{
    use EnvHelper;


    /**
     * 取得首頁資料
     * @return array
     */
    public function home()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/home');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);
        dd($order);
        return $order;
    }

    /**
     * 取得廣告左右滿版資料
     * @return array
     */
    public function ads()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/home');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data'],'all','ads');
        dd($order);
        return $order;
    }

    /**
     * 取得熱門探索資料
     * @return array
     */
    public function exploration()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/home');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data'],'all','exploration');
        dd($order);
        return $order;
    }

    /**
     * 取得自訂版位資料
     * @return array
     */
    public function customize()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/home');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data'],'all','customize');
        dd($order);
        return $order;
    }

    /**
     * 取得底部廣告Banner
     * @return array
     */
    public function banner()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/home');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data'],'all','banner');
        dd($order);
        return $order;
    }

    /**
     * 取得標籤資料
     * @return array
     */
    public function info()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/info');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);

        return $order;
    }


    /**
 * 利用目錄id取得目錄資料
 * @param $itemId
 * @return LayoutResult
 */
    public function category($itemId)
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/category');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);

        return $order;
    }

    /**
     * 利用選單id取得選單資料
     * @param $itemId
     * @return LayoutResult
     */
    public function menu($itemId)
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/menu');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);

        return $order;
    }
}