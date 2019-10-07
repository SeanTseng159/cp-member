<?php

namespace App\Repositories;

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


}
