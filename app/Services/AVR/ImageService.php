<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\AVR;



use App\Repositories\AVR\ImageRepository;
use App\Services\BaseService;


class ImageService extends BaseService
{
    protected $repository;


    /**
     * ActivityService constructor.
     *
     * @param ImageRepository $repository
     */
    public function __construct(ImageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     */
    public function list()
    {
        return $this->repository->list();
    }


    public function path($modelType, $modeSpecID, $sort = null)
    {
        return $this->repository->path($modelType, $modeSpecID, $sort);
    }


}
