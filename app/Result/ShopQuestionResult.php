<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result;

use App\Enum\WaitingStatus;
use App\Helpers\CommonHelper;
use App\Helpers\DateHelper;
use App\Helpers\ImageHelper;
use App\Traits\ShopHelper;
use Carbon\Carbon;


class ShopQuestionResult
{
    use shopHelper;


    public function get($shop)
    {
        $result = new \stdClass;

        $result->id = $shop->currentQuestion->id;

        $result->list = [];
        foreach ($shop->currentQuestion->topicList as $topic)
        {
            $question = new \stdClass;
            $question->id = $topic->id;
            $question->type = $topic->type;
            $question->title = $topic->title;
            $question->options = $topic->options;
            $result->list[] = $question;
        }
        return $result;
    }


}
