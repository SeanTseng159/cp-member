<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\LayoutAdRepository;

class LayoutService extends BaseService
{
    protected $layoutAdRepository;

    public function __construct(LayoutAdRepository $layoutAdRepository)
    {
        $this->layoutAdRepository = $layoutAdRepository;
    }

    /**
     * 取首頁資料
     * @param $id
     * @param $memberId
     * @return mixed
     */
    public function home()
    {
        return $this->layoutAdRepository->getSlide();
    }
}
