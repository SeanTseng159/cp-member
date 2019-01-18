<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use Carbon\Carbon;

class DiningCarMenuResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取菜單列表
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
            $newMenus[] = $this->getMenu($i);
        }

        return $newMenus;
    }

    /**
     * 菜單資訊
     * @param $blog
     */
    private function getMenu($i, $isDetail = false)
    {
        $result = new \stdClass;
        $result->categoryName = '主餐';
        $result->items = $this->getItems();

        return $result;
    }

    /**
     * 取菜單列表
     * @param $data
     */
    private function getItems()
    {
        for ($i=1; $i < 3; $i++) {
            $newItems[] = $this->getItem($i);
        }

        return $newItems;
    }

    /**
     * 取菜單列表
     * @param $data
     */
    public function getItem($i, $isDetail = false)
    {
        $result = new \stdClass;
        $result->id = $i;
        $result->name = '黯然銷魂飯';
        $result->price = 140;

        if ($isDetail) {
            $result->categoryName = '主餐';
            $result->imgs = [
                'https://s.yimg.com/ny/api/res/1.2/7Rd_dCdx5PA7HrPfsw3ICA--~A/YXBwaWQ9aGlnaGxhbmRlcjtzbT0xO3c9NjAwO2g9NDUwO2lsPXBsYW5l/http://media.zenfs.com/en_us/News/skypost/12ED005__20180918_L.jpg',
                'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFQT7cowu9fNTDdII3MmeNg8-QuFrgmnohHI6LZDhPvYMBflRt',
                'https://d3h1lg3ksw6i6b.cloudfront.net/media/image/2019/01/01/a058f8cc9dcc4f29ab0b8e7b4db02baf_45906113_496386950872933_2808875978422484992_o.jpg'
            ];
            $result->content = '【蜜汁叉燒煎蛋飯】本店人氣餐點，香軟叉燒搭配溏心太陽蛋 淋上特製醬汁…香軟白飯裏著蛋汁，再配上鬆軟叉燒肉。';
        }
        else {
            $result->img = 'https://s.yimg.com/ny/api/res/1.2/7Rd_dCdx5PA7HrPfsw3ICA--~A/YXBwaWQ9aGlnaGxhbmRlcjtzbT0xO3c9NjAwO2g9NDUwO2lsPXBsYW5l/http://media.zenfs.com/en_us/News/skypost/12ED005__20180918_L.jpg';
        }

        return $result;
    }
}
