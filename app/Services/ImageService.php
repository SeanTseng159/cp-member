<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services;


use App\Repositories\ImageRepository;





class ImageService extends BaseService
{
    protected $repository;

    public function __construct(ImageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取詳細image資料
     *
     * @param $modelType
     * @param $modeSpecID
     *
     * @param $sort
     *
     * @return mixed
     */
    public function path($modelType, $modeSpecID, $sort = null)
    {
        return $this->repository->path($modelType, $modeSpecID, $sort);
    }
}
