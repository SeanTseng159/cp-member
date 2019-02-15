<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use Carbon\Carbon;
use App\Traits\StringHelper;
use App\Helpers\ImageHelper;

class DiningCarBlogResult extends BaseResult
{
    use StringHelper;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取動態消息列表
     * @param $data
     */
    public function list($newsfeeds)
    {
        if ($newsfeeds->isEmpty()) return [];

        $newBlogs = [];
        foreach ($newsfeeds as $newsfeed) {
            $newBlogs[] = $this->getNewsFeed($newsfeed);
        }

        return $newBlogs;
    }

    /**
     * 動態消息資訊
     * @param $newsfeed
     */
    public function getNewsFeed($newsfeed, $isDetail = false)
    {
        if (!$newsfeed) return null;

        $result = new \stdClass;
        $result->id = $newsfeed->id;
        $result->diningCarId = $newsfeed->dining_car_id;
        $result->title = $newsfeed->title;
        $result->date = $newsfeed->release_time;

        if ($isDetail) {
            $result->imgs = ImageHelper::urls($newsfeed->imgs);
            $result->content = $newsfeed->content;
        }
        else {
            $result->img = ImageHelper::url($newsfeed->mainImg);
            $result->content = $this->outputStringLength($newsfeed->content, 15);
        }

        return $result;
    }
}
