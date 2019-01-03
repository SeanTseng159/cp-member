<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;

class AddressController extends RestLaravelController
{
    protected $lang = 'zh-TW';

    public function __construct()
    {
    }

    /**
     * 縣市列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function counties()
    {
        $result = [
            '高雄市',
            '台南市',
            '屏東縣',
            '台北市',
            '新北市',
            '基隆市',
            '宜蘭縣',
            '新竹市',
            '新竹縣',
            '桃園市',
            '苗栗縣',
            '台中市',
            '彰化縣',
            '南投縣',
            '雲林縣',
            '嘉義市',
            '嘉義縣',
            '台東縣',
            '花蓮縣',
            '澎湖縣',
            '金門縣',
            '連江縣'
        ];

        return $this->success($result);
    }
}
