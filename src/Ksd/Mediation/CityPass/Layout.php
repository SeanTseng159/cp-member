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
     * @return LayoutResult
     */
    public function home()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/home');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {
        }

        if (isset($result['data'])) {

            $order = new LayoutResult();
            $order->cityPass($result['data']);
            return $order;
        }

        return null;
    }

    /**
     * 取得廣告左右滿版資料
     * @return LayoutResult
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
        $order->cityPass($result['data'],true,'ads');

        return $order;
    }

    /**
     * 取得熱門探索資料
     * @return LayoutResult
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
        $order->cityPass($result['data'],true,'exploration');

        return $order;
    }

    /**
     * 取得自訂版位資料
     * @return LayoutResult
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
        $order->cityPass($result['data'],true,'customize');

        return $order;
    }

    /**
     * 取得底部廣告Banner
     * @return LayoutResult
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
        $order->cityPass($result['data'],true,'banner');

        return $order;
    }

    /**
     * 取得標籤資料
     * @return LayoutResult
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
    public function category($parameter)
    {

        $result = [];
        try {
            $itemId = $parameter->id;
            $response = $this->putQuery('page', $parameter->page)
                ->request('GET', 'layout/category/'.$itemId);
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        return $result['data'];
    }

    /**
     * 取得選單資料
     * @return LayoutResult
     */
    public function menu($itemId)
    {

        $result = [];
        try {
            $response = ($itemId) ? $this->request('GET', 'layout/menu/' . $itemId) : $this->request('GET', 'layout/menu');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        if ($itemId) {
            return $result['data'];
        }
        else {
            $order = new LayoutResult();
            $order->cityPass($result['data'],true,'menu');

            return $order;
        }
    }

    /**
     * 利用選單id取得商品資料
     * @param $itemId
     * @return LayoutResult
     */
    public function maincategory($itemId)
    {

        $result = [];
        try {
            $response = $this->request('GET', 'product/category/' . $itemId);
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        return $result['data'];
    }

    /**
     * 利用選單id取得商品資料
     * @param $itemId
     * @return LayoutResult
     */
    public function subcategory($parameter)
    {

        $result = [];
        try {
            $itemId = $parameter->id;
            $response = $this->putQuery('page', $parameter->page)
                ->request('GET', 'product/subcategory/'.$itemId);
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        return $result['data'];
    }


    /**
     * 取得主標籤id
     * @return array
     */
    public function getCategoryId()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/menu');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $categoryId=[];
        foreach ($result['data'] as $items) {
            $categoryId[] = $items['id'];
        }
        return isset($categoryId) ? $categoryId : null;

    }

    /**
     * 取得子標籤id
     * @return array
     */
    public function getSubCategoryId()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'layout/menu');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {

        }

        $subCategoryId=[];
        foreach ($result['data'] as $items) {
            foreach ($items['items'] as $item) {
                $subCategoryId[] = $item['id'];
            }
        }

        return isset($subCategoryId) ? $subCategoryId : null;

    }


}
