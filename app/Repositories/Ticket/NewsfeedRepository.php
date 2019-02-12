<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

use App\Models\Ticket\Newsfeed;

class NewsfeedRepository extends BaseRepository
{
    protected $date;

    public function __construct(Newsfeed $model)
    {
        $this->date = Carbon::now()->toDateTimeString();

        $this->model = $model;
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

        return $this->model->with(['mainImg'])
                            ->where('dining_car_id', $params['diningCarId'])
                            ->where('status', 1)
                            ->where('onshelf_time', '<=', $this->date)
                            ->where('offshelf_time', '>=', $this->date)
                            ->orderBy('sort', 'asc')
                            ->paginate($params['limit']);
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id = 0)
    {
        return $this->model->with(['imgs'])
                            ->find($id);
    }
}
