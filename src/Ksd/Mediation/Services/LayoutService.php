<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/28
 * Time: 下午 05:23
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Repositories\LayoutRepository;

class LayoutService
{
    use MemberHelper;

    private $repository;

    public function __construct()
    {
        $this->repository = new LayoutRepository();
    }

    /**
         * 取得首頁資料
         * @return mixed
         */
    public function home()
    {
        return $this->repository->home();
    }

    /**
         * 取得廣告左右滿版資料
         * @return mixed
         */
    public function ads()
    {
        return $this->repository->ads();
    }

    /**
         * 取得熱門探索資料
         * @return mixed
         */
    public function exploration()
    {
        return $this->repository->exploration();
    }

    /**
     * 取得自訂版位資料
     * @return mixed
     */
    public function customize()
    {
        return $this->repository->customize();
    }

    /**
     * 取得底部廣告Banner
     * @return mixed
     */
    public function banner()
    {
        return $this->repository->banner();
    }

    /**
     * 取得標籤資料
     * @return mixed
     */
    public function info()
    {
        return $this->repository->info();
    }

    /**
         * 利用目錄id取得目錄資料
         * @param  $parameter
         * @return mixed
         */
    public function category($parameter)
    {
        return $this->repository->category($parameter);
    }

    /**
     * 取得下拉 選單資料
     * @param  $parameter
     * @return mixed
     */
    public function menu()
    {
        return $this->repository->menu();
    }


}