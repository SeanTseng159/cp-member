<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\LinepayStore;

class LinepayStoreRepository
{
    protected $model;

    public function __construct(LinepayStore $model)
    {
        $this->model = $model;
    }
    
    public function getStores($longitude, $latitude)
    {
        return $this->model->withinLocation($longitude, $latitude)->get();
    }
}
