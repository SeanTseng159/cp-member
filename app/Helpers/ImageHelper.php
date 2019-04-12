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
     * @param $size
     *
     * @return string
     */
    public static function url($img, $size = '')
    {
        if (!$img) return '';

        $filePath = '';

        if ($size) {
            $filePath = SELF::getFitImage($img, $size);
        }
        else {
            if (Agent::isMobile()) {
                $filePath = SELF::getFitImage($img, 's');
            } elseif (Agent::isTablet()) {
                $filePath = SELF::getFitImage($img, 'm');
            } else {
                $filePath = SELF::getFitImage($img, 'b');
            }
        }

        return $filePath;
    }

    /**
     * 取所有照片
     *
     * @param $imgs
     * @param $size
     *
     * @return array
     */
    public static function urls($imgs, $size = '')
    {
        if ($imgs->isEmpty()) return [];

        $urls = [];
        foreach ($imgs as $img) {
            $urls[] = SELF::url($img, $size);
        }

        return $urls;
    }

    private static function getFitImage($img, $size = 'b')
    {
        $info = json_decode($img->compressed_info);

        $hasCompressedInfo = ($img->compressed_info && isset($info->compressed_sizes->{$size}));

        // 取遠端圖片
        if ($hasCompressedInfo) {

            if (env('USE_CDN_IMAGE', false)) {
                $cdnImg = $info->image_hosting_urls->{$size};

                if ($cdnImg) return $cdnImg;
            }

            // 如遠端無圖，取本地
            $localImg = sprintf('%s%s_%s.%s', $img->folder, $img->filename, $size, $img->ext);
            return CommonHelper::getBackendHost($localImg);
        }
        else {
            $localImg = sprintf('%s%s.%s', $img->folder, $img->filename, $img->ext);
            return CommonHelper::getBackendHost($localImg);
        }
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
            $returnAry[] = self::url($path, 's');
        }

        if (count($returnAry) == 1) {
            return $returnAry[0];
        }

        return $returnAry;
    }

}
