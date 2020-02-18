<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use App\Models\Ticket\DiningCar;

class DiningCarRepository extends BaseRepository
{
    protected $type = 1;

    public function __construct(DiningCar $model)
    {
        $this->model = $model;
    }

    public function setStoreType($type)
    {
        $this->type = $type;
        return $this;

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


        return $this->model->with(['category', 'subCategory', 'mainImg','employee.supplier'])
            ->where('status', 1)
            ->where('type', $this->type)
            ->when($params['keyword'], function ($query) use ($params) {
                $query->where('name', 'like', '%' . $params['keyword'] . '%')
                    ->orWhereIn('id', $params['keywordDiningCarIds'])
                    ->where('status', 1);
            })
            ->when($params['county'], function ($query) use ($params) {
                $query->where('county', $params['county']);
            })
            ->when($params['category'], function ($query) use ($params) {
                $query->where('dining_car_category_id', $params['category']);
            })
            ->where(function ($query) use ($params) {
                if (in_array($params['openStatus'], ['0', '1', '2'])) {
                    $query->where('open_status', $params['openStatus']);
                }
            })
            ->when($params['service'], function ($query) use ($params) {
                //前端  0代表:會員集點   1代表:線上訂位  2代表現場後位 3代表線上點餐
                //後端  1代表:會員集點   2代表:線上訂位  3代表現場後位 4代表線上點餐
                //因為0的出現會導致一些問題所以這樣設定
                if ($params['service'] == 1) {
                    $query->where('level', 1)->where('expired_at', '>=', Carbon::today());
                } elseif ($params['service'] == 2) {
                    $query->where('canBooking', 1);
                } elseif ($params['service'] == 3) {
                    $query->where('canWaiting', 1);
                } elseif ($params['service'] == 4) {
                    $query->where('canOrdering', 1);
                }
            })
            ->where('type', $this->type)
            ->orderBy('updated_at','desc')
            ->paginate($params['limit']);
    }

    /**
     * 取地圖列表
     * @param  $params
     * @return mixed
     */
    public function map($params = [])
    {
        return $this->model->with(['category', 'subCategory', 'mainImg','employee.supplier'])
            ->where('status', 1)
            ->when($params['keyword'], function ($query) use ($params) {
                $query->where('name', 'like', '%' . $params['keyword'] . '%')
                    ->orWhereIn('id', $params['keywordDiningCarIds'])
                    ->where('status', 1);
            })
            ->when($params['category'], function ($query) use ($params) {
                $query->where('dining_car_category_id', $params['category']);
            })
            ->where(function ($query) use ($params) {
                if (in_array($params['openStatus'], ['0', '1', '2'])) {
                    $query->where('open_status', $params['openStatus']);
                }
            })
            ->where('type', $this->type)
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
        return $this->model->with([
            'category',
            'subCategory',
            'mainImg',
            'imgs',
            'socialUrls',
            'businessHoursDays.times',
            'businessHoursDates',
            'media',
            'memberCard' => function ($query) use ($memberId) {
                $query->where('member_id', $memberId);
            },
            'memberLevels',
            'currentQuestion',
            'employee.supplier'
        ])
            ->withCount(['gifts', 'newsfeeds', 'coupons'])
            ->whereId($id)
            ->where('status', 1)
//            ->where('type',$this->type)
            ->first();
    }

    public function getDiningCarByShorterUrlId($shorterUrlId)
    {
        return $this->model->select('id')
            ->where('level', '>', 0)// 短網址為付費功能
            ->where(function ($q) {
                $q->where('expired_at', '>=', Carbon::now())
                    ->orWhere('expired_at', null);
            })
            ->where('shorter_url_id', $shorterUrlId)
            ->whereNotNull('shorter_url_id')
            ->first();
    }

    public function easyFind($id)
    {
        return $this->model->with('employee.supplier')
            ->where('id', $id)
            ->where('status', 1)
            ->first();
    }
}
