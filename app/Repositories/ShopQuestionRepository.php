<?php

namespace App\Repositories;

use App\Enum\ShopQuestionType;
use App\Models\MemberShopQuestion;
use App\Models\ShopQuestion;
use App\Models\ShopQuestionDetail;
use App\Models\Ticket\DiningCar;

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

    public function checkAnswer($versionId, $answerAry)
    {

        $answerOptions = $this->detail
            ->where('question_id', $versionId)
            ->get();

        foreach ($answerAry as $pair) {
            $id = $pair->id;
            $answer = $pair->answer;
            $filtered = $answerOptions->filter(function ($item) use ($id) {
                return $item->id == $id;
            });
            $only = $filtered->first();
            if ($only->type != ShopQuestionType::QA) {
                $ansAry = explode(',', $answer);
                $optionAry = explode(",", $only->options);
                $answerPostion = [];
                foreach ($ansAry as $ans) {
                    $idx = array_search($ans, $optionAry);
                    if ($idx === false) {
                        throw new \Exception("選項不包含{$ans}");
                    }
                    $answerPostion[] = $idx;
                }
                $pair->answerPostion = count($answerPostion) <= 1 ? (int)implode("", $answerPostion) : $answerPostion;
            }
        }
        return $answerAry;
    }

    public function store($memberId, $date, $answerAry)
    {
        $insertArry = [];
        foreach ($answerAry as $ans) {
            $insert = [
                'question_detail_id' => $ans->id,
                'member_id' => $memberId,
                'value' => $ans->answer,
                'consumption' => $date
            ];
            $insertArry[] = $insert;
        }
        return $this->memberAnswer->insert($insertArry);

    }
}
