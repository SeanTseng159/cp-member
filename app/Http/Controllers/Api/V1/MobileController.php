<?php
/**
 * User: lee
 * Date: 2018/12/26
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Hashids\Hashids;
use App\Traits\ValidatorHelper;

class MobileController extends RestLaravelController
{
    use ValidatorHelper;

    public function __construct()
    {

    }

    /**
     * 手機解碼
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function decode(Request $request)
    {
        try {
            $country = $request->input('country');
            $code = $request->input('code');

            if (!$country || !$code) return $this->failureCode('E0001');

            $phoneNumber = (new Hashids('PhoneNumber', 20))->decode($code);
            if (!$phoneNumber) return $this->failureCode('E0301');

            $phoneNumber = $this->VerifyPhoneNumber($country, $phoneNumber[0], $phoneNumber[1]);
            if (!$phoneNumber) return $this->failureCode('E0301');

            return $this->success($phoneNumber);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
