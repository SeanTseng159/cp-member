<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 05:30
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Repositories\NotificationRepository;

class NotificationService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new NotificationRepository();
    }
}