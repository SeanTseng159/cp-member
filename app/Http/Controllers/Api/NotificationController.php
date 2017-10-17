<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 05:18
 */

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\NotificationService;

class NotificationController extends RestLaravelController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function register(Request $request){

        

    }

}