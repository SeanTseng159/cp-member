<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\LayoutAppRepository as AppRepository;

class LayoutAppService extends BaseService
{
    protected $appRepository;

    public function __construct(AppRepository $appRepository)
    {
        $this->appRepository = $appRepository;
    }

    /**
     * 取全部
     * @return mixed
     */
    public function all()
    {
        return $this->appRepository->all();
    }

    /**
     * 取顯示於首頁app
     * @return mixed
     */
    public function findInHome()
    {
        return $this->appRepository->findInHome();
    }
}
