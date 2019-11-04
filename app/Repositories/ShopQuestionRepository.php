<?php

namespace App\Repositories;

use App\Enum\ShopQuestionType;
use App\Models\MemberShopQuestion;
use App\Models\ShopQuestion;
use App\Models\ShopQuestionDetail;
use App\Models\Ticket\DiningCar;
use Carbon\Carbon;

class ShopQuestionRepository extends BaseRepository
{

    protected $model;
    protected $diningCarModel;
    protected $detail;
    protected $memberAnswer;


    public function __construct(ShopQuestion $model, DiningCar $diningCarModel, ShopQuestionDetail $detailModel,
                                MemberShopQuestion $memberShopQuestion)
    {
        $this->model = $model;
        $this->diningCarModel = $diningCarModel;
        $this->detail = $detailModel;
        $this->memberAnswer = $memberShopQuestion;
    }

    public function get($shopId)
    {
        return $this->diningCarModel
            ->where('id', $shopId)->with('currentQuestion', 'currentQuestion.topicList')
            ->first();
    }

    public function getQuestionDetail($shopId)
    {
        return $this->detail
            ->where('id', $shopId)->with('currentQuestion', 'currentQuestion.topicList')
            ->first();
    }

    public function checkAnswer($versionId, $postAry)
    {

        $answerOptions = $this->detail
            ->where('question_id', $versionId)
            ->get();

        foreach ($answerOptions as $option) {
            $id = $option->id;
            $required = $option->required;
            $title = $option->title;
            $type = $option->type;

            $filtered = $postAry->filter(function ($item) use ($id) {
                return $item->id == $id;
            });

            if ($required && $filtered->isEmpty()) {
                throw new \Exception("【{$title}】必填");
            }

            if ($filtered->isEmpty())
                continue;

            if ($filtered->count() > 1)
                throw new \Exception("【{$title}】重複輸入");

            $answer = $filtered->first();
            if ($type != ShopQuestionType::QA) {

                $ansAry = explode(',', $answer->answer);
                $optionAry = explode(",", $option->options);
                foreach ($ansAry as $ans) {
                    $idx = array_search($ans, $optionAry);
                    if ($idx === false) {
                        throw new \Exception("選項不包含{$ans}");
                    }
                }
            }
        }
        return $postAry;
    }

    public function store($memberId, $date, $answerAry)
    {
        $insertArry = [];
        foreach ($answerAry as $ans) {
            $insert = [
                'question_detail_id' => $ans->id,
                'member_id' => $memberId,
                'value' => is_null($ans->answer) ? "" : $ans->answer,
                'consumption' => $date,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ];
            $insertArry[] = $insert;
        }
        return $this->memberAnswer->insert($insertArry);

    }
}
