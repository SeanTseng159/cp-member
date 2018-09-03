<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ServiceRepository;

class ServiceService extends BaseService
{
    protected $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * 取常見問題
     * @param $lang
     * @return mixed
     */
    public function faq($lang = 'zh-TW')
    {
        return $this->serviceRepository->all($lang);
    }
}
