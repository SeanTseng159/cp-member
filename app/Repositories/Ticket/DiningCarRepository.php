<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\Paginator;
use App\Models\Ticket\DiningCar;

class DiningCarRepository extends BaseRepository
{
    private $limit = 20;

    public function __construct(DiningCar $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 取列表
     * @param  $params
     * @return mixed
     */
    public function list($params = [])
    {
        $currentPage = $params['page'];
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });


        return $this->missionModel->with(['category', 'subCategory', 'mainImg'])
                            ->where('status', 1)
                            ->when($params['keyword'], function($query) use ($params) {
                                $query->where('name', 'like', '%' . $params['keyword'] . '%')
                                    ->orWhereIn('id', $params['keywordDiningCarIds'])
                                    ->where('status', 1);
                            })
                            ->when($params['county'], function($query) use ($params) {
                                $query->where('county', $params['county']);
                            })
                            ->when($params['category'], function($query) use ($params) {
                                $query->where('dining_car_category_id', $params['category']);
                            })
                            ->where(function($query) use ($params) {
                                if (in_array($params['openStatus'], ['0', '1', '2'])) {
                                    $query->where('open_status', $params['openStatus']);
                                }
                            })
                            ->paginate($params['limit']);
    }

    /**
     * 取地圖列表
     * @param  $params
     * @return mixed
     */
    public function map($params = [])
    {
        return $this->missionModel->with(['category', 'subCategory', 'mainImg'])
                            ->where('status', 1)
                            ->when($params['keyword'], function($query) use ($params) {
                                $query->where('name', 'like', '%' . $params['keyword'] . '%')
                                    ->orWhereIn('id', $params['keywordDiningCarIds'])
                                    ->where('status', 1);
                            })
                            ->when($params['category'], function($query) use ($params) {
                                $query->where('dining_car_category_id', $params['category']);
                            })
                            ->where(function($query) use ($params) {
                                if (in_array($params['openStatus'], ['0', '1', '2'])) {
                                    $query->where('open_status', $params['openStatus']);
                                }
                            })
                            ->withinLocation($params['range']['longitude'], $params['range']['latitude'])
                            ->get();
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id = 0, $memberId = 0)
    {
        return $this->missionModel->with([
                                'category',
                                'subCategory',
                                'mainImg',
                                'imgs',
                                'socialUrls',
                                'businessHoursDays.times',
                                'businessHoursDates',
                                'media',
                                'memberCard' => function($query) use ($memberId) {
                                    $query->where('member_id', $memberId);
                                },
                                'memberLevels'
                            ])
                            ->whereId($id)
                            ->where('status', 1)
                            ->first();
    }

    public function getDiningCarByShorterUrlId($shorterUrlId)
    {
        return $this->missionModel->select('id')
                           ->where('level', '>', '0')              // 短網址為付費功能
                           ->where('expired_at', '>=', Carbon::now())
                           ->where('shorter_url_id', $shorterUrlId)
                           ->whereNotNull('shorter_url_id')
                           ->first();
    }
}
