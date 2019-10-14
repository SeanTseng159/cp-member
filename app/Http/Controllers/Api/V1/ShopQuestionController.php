<?php

namespace App\Http\Controllers\Api\V1;


use App\Core\Logger;
use App\Result\ShopQuestionResult;
use App\Services\ShopQuestionService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class ShopQuestionController extends RestLaravelController
{
    protected $service;


    public function __construct(ShopQuestionService $service)
    {
        $this->service = $service;
    }

    public function get(Request $request, $shopId)
    {

        try {

            if (is_null($shopId))
                throw new \Exception('查不到店鋪資料');

            $shop = $this->service->get($shopId);

            if (!$shop->canQuestionnaire)
                throw new \Exception('店鋪尚未開放問卷');

            if (is_null($shop->currentQuestion))
                throw new \Exception('店鋪尚未設定問卷資料或沒有進行中的問卷');

            if (count(optional($shop->currentQuestion)->topicList) < 1)
                throw new \Exception('店鋪尚未設定問卷題目');

            $data = (new ShopQuestionResult())->get($shop);

            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopQuestionController::get', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }
    }

    public function create(Request $request, $shopId, $versionId)
    {


        try {


            $validator = \Validator::make($request->all(),
                [
                    'date' => 'required|date_format:Y-m-d',
                    'list' => 'required|array|min:1',
//                    'list.*.id' => 'required|integer',
//                    'list.*.answer' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new \Exception($validator->messages());
            }
            $shop = $this->service->get($shopId);

            if (!$shop->canQuestionnaire)
                throw new \Exception('店鋪尚未開放問卷');

            if (is_null($shop->currentQuestion))
                throw new \Exception('店鋪尚未設定問卷資料或沒有進行中的問卷');

            if (count(optional($shop->currentQuestion)->topicList) < 1)
                throw new \Exception('店鋪尚未設定問卷題目');

            $date = $request->input('date');
            $list = $request->input('list');
            $memberId = $request->memberId;

            $ary = [];
            foreach ($list as $item) {
                $ary[] = (object)$item;
            }
            //確認answer
            $answerAry = $this->service->checkAnswer($versionId, collect($ary));
            $data = $this->service->store($memberId, $date, $answerAry);
            return $this->success($data);
        } catch (\Exception $e) {
//            dd($e);
            Logger::error('ShopQuestionController::create', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }
    }


}