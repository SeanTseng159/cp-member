<?php
/**
 * User: Annie
 * Date: 2019/02/15
 * Time: 上午 10:03
 */

namespace App\Repositories;

use App\Models\Image;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;

class ImageRepository extends BaseRepository
{
    private $limit = 20;
    
    
    public function __construct(Image $model)
    {
        $this->model = $model;
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
    public function path($modelType,$modeSpecID,$sort)
    {
        $result = $this->model
            ->select('folder', 'filename', 'ext','compressed_info')
            ->where('model_type', $modelType)
            ->where('model_spec_id', $modeSpecID)
            ->when($sort,function ($query) use($sort) {
                    return $query->where('sort', $sort);
                })
            ->get();
        
        
        if ($result->count() == 0)
        {
            return "";
        }
        
        return $result;
//        else if ($result->count() === 1)
//        {
//            return $result->first();
////            return $this->getPath($result);
//
//        }
//        else
//        {
//            $pathAry = [];
//            foreach ($result as $item)
//            {
//                $path[] = $this->getPath($item);
//            }
//
//            return $pathAry;
//        }
        
        
    }
    
    private function getPath($model)
    {
        return $model->folder.$model->filename.$model->ext;
    }
   
    
    
}
