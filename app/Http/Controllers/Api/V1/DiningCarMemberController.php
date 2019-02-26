<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Traits\CryptHelper;
use Hashids\Hashids;

use App\Services\Ticket\DiningCarMemberService;
use App\Services\Ticket\DiningCarService;
use App\Services\MemberService;

use App\Parameter\Ticket\DiningCarMemberParameter;
use App\Result\Ticket\DiningCarMemberResult;

class DiningCarMemberController extends RestLaravelController
{
    use CryptHelper;

    protected $service;

    public function __construct(DiningCarMemberService $service)
    {
        $this->service = $service;
    }

    /**
     * 加入餐車會員
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        try {
            $memberId = $request->memberId;
            $diningCarId = $request->input('diningCarId');

            if (!$diningCarId) return $this->failureCode('E0201');
            $diningCarId = $this->decryptHashId('DiningCar', $diningCarId);

            // 是否加入會員
            $isMember = $this->service->isMember($memberId, $diningCarId);
            if ($isMember) return $this->failureCode('A0101');

            $result = $this->service->add($memberId, $diningCarId);
            // todo: 取禮物

            // 取會員卡資料
            $diningCarMember = $this->service->find($memberId, $diningCarId);
            $memberCard = (new DiningCarMemberResult)->getMemberCard($diningCarMember);

            return ($result) ? $this->success([
                                        'gift' => null,
                                        'memberCard' => $memberCard
                                    ]) : $this->failureCode('E0200');
        } catch (Exception $e) {
            return $this->failureCode('E0200');
        }
    }

    /**
     * 取使用者已加入會員的餐車
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diningCars(Request $request)
    {
        try {
            $params = (new DiningCarMemberParameter($request))->list();

            $data = $this->service->list($request->memberId, $params);

            $result['page'] = (int) $params['page'];
            $result['total'] = $data->total();
            $result['cars'] = (new DiningCarMemberResult)->list($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 餐車&會員資料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request, DiningCarService $diningCarService, MemberService $memberService, $id = '')
    {
        try {
            $params = (new DiningCarMemberParameter($request))->info();

            $diningCarId = (new Hashids('DiningCar', 6))->decode($id);
            $memberToken = (new Hashids('Member', 12))->decode($params['token']);

            if (!$diningCarId || !$memberToken) return $this->failureCode('E0001');

            $isMember = $this->service->isMember($memberToken[0], $diningCarId[0]);
            if ($isMember) return $this->failureCode('A0101');

            $diningCar = $diningCarService->find($diningCarId[0]);
            $member = $memberService->find($memberToken[0]);

            return $this->success([
                    'car' => [
                        'id' => $diningCar->id,
                        'hashId' => $this->encryptHashId('DiningCar', $diningCarId[0]),
                        'name' => $diningCar->name
                    ],
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name
                    ]
                ]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 餐車&會員資料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function invite(Request $request)
    {
        try {
            $params = (new DiningCarMemberParameter($request))->invite();

            $diningCarId = $this->decryptHashId('DiningCar', $params['diningCarId']);

            // 是否加入會員
            $isMember = $this->service->isMember($params['memberId'], $diningCarId);
            if ($isMember) return $this->failureCode('A0101');


            $result = $this->service->add($params['memberId'], $diningCarId);
            // todo: 取禮物

            // 取會員卡資料
            $diningCarMember = $this->service->find($params['memberId'], $diningCarId);
            $memberCard = (new DiningCarMemberResult)->getMemberCard($diningCarMember);

            return ($result) ? $this->success([
                                        'gift' => null,
                                        'memberCard' => $memberCard
                                    ]) : $this->failureCode('E0200');
        } catch (Exception $e) {
            return $this->failureCode('E0200');
        }
    }
}
