<?php
/**
 * Created by PhpStorm.
 * User: Danny
 * Date: 2019/7/25
 * Time: 上午 09:17
 */

namespace App\Repositories;


use App\Models\Notification;

class NotificationRepository extends BaseRepository
{
    protected $model;
    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    public function getDurationNotification($minute)
    {
        return $this->model->currentNotification($minute);
    }

}