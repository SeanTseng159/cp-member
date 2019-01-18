<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use Carbon\Carbon;

class DiningCarBlogResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取動態消息列表
     * @param $data
     */
    public function list()
    {
        /*if (!$cars) return [];

        $newBlogs = [];
        foreach ($cars as $car) {
            $newBlog = $this->getCar($car);
            if ($newBlogs) $newBlogs[] = $newBlog;
        }*/

        for ($i=1; $i < 5; $i++) {
            $newBlogs[] = $this->getBlog($i);
        }

        return $newBlogs;
    }

    /**
     * 動態消息資訊
     * @param $blog
     */
    public function getBlog($i, $isDetail = false)
    {
        $result = new \stdClass;
        $result->id = $i;
        $result->title = '活動標題活動標題';
        $result->date = '2018/05/31';
        $result->img = 'https://weibyapps.files.wordpress.com/2017/05/635.jpg?w=1000';

        if ($isDetail) {
            $result->content = '活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題  <br> 活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題 <br><br><br> 活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題';
        }
        else {
            $result->content = '活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題活動標題';
        }

        return $result;
    }
}
