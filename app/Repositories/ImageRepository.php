<?php
/**
 * User: Annie
 * Date: 2019/02/15
 * Time: 上午 10:03
 */

namespace App\Repositories;

use App\Models\Image;


class ImageRepository extends BaseRepository
{
    private $limit = 20;


    public function __construct(Image $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 取得圖片的路徑，若sort == null，則回傳所有相關的圖片
     *
     * @param $modelType
     * @param $modeSpecID
     *
     * @param $sort 排序
     *
     * @return mixed
     */
    public function path($modelType, $modeSpecID, $sort = null)
    {
        $result = $this->missionModel
            ->select('folder', 'filename', 'ext', 'compressed_info')
            ->where('model_type', $modelType)
            ->where('model_spec_id', $modeSpecID)
            ->when($sort, function ($query) use ($sort) {
                return $query->where('sort', $sort);
            })
            ->get();



        if ($result->count() == 0) {
            return "";
        }

        return $result;


    }


}
