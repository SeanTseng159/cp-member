<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 9:42
 */

namespace App\Helpers;

use Agent;
use App\Models\Image;
use App\Repositories\ImageRepository;
use App\Services\ImageService;


Class ImageHelper
{
    static $imageService;
    
    
    /**
     * 取單一照片
     *
     * @param $img
     *
     * @return string
     */
    public static function url($img)
    {
        if (!$img) return '';

        $filePath = '';

        if (Agent::isMobile()) {
            $filePath = SELF::getFitImage($img, 'm');
        } elseif (Agent::isTablet()) {
            $filePath = SELF::getFitImage($img, 'm');
        } else {
            $filePath = SELF::getFitImage($img, 'b');
        }

        return CommonHelper::getBackendHost($filePath);
    }

    /**
     * 取所有照片
     *
     * @param $imgs
     *
     * @return array
     */
    public static function urls($imgs)
    {
        if ($imgs->isEmpty()) return [];

        $urls = [];
        foreach ($imgs as $img) {
            $urls[] = SELF::url($img);
        }

        return $urls;
    }

    private static function getFitImage($img, $size = 'b')
    {
        $info = json_decode($img->compressed_info);

        if ($img->compressed_info && isset($info->compressed_sizes->{$size})) {
            $filePath = (env('USE_CDN_IMAGE')) ? $filePath = $info->image_hosting_urls->{$size} : sprintf('%s%s_%s.%s', $img->folder, $img->filename, $size, $img->ext);
        } else {
            $filePath = sprintf('%s%s.%s', $img->folder, $img->filename, $img->ext);
        }

        return $filePath;
    }

    private static function getInstance()
    {

        if (is_null(self::$imageService)) {

            self::$imageService = new ImageService(new ImageRepository(new Image));

        }

        return self::$imageService;

    }

    /**
     * 取得table : images內的圖片網址，若sort有值，則回傳path字串，否則會傳path string array
     *
     * @param $model_type
     * @param $model_spec_id
     * @param $sort
     *
     * @return string|array
     */

    static public function getImageUrl($model_type, $model_spec_id, $sort = null)
    {
        self::getInstance();

        $pathResult = self::$imageService->path($model_type, $model_spec_id, $sort);

        if ($pathResult == '') {
            return "";
        }


        $returnAry = [];
        foreach ($pathResult as $path) {
            $returnAry[] = self::url($path);
        }

        if (count($returnAry) == 1) {
            return $returnAry[0];
        }

        return $returnAry;
    }

}
