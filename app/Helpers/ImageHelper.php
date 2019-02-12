<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 9:42
 */

namespace App\Helpers;

use Agent;
use App\Helpers\CommonHelper;

Class ImageHelper
{
    /**
    * 取單一照片
    * @param $img
    */
    public static function url($img)
    {
        if (!$img) return '';

        $filePath = '';

        if (Agent::isMobile()) {
            $filePath = SELF::getFitImage($img, 'm');
        }
        elseif (Agent::isTablet()) {
            $filePath = SELF::getFitImage($img, 'm');
        }
        else {
            $filePath = SELF::getFitImage($img, 'b');
        }

        return CommonHelper::getBackendHost($filePath);
    }

    /**
    * 取所有照片
    * @param $img
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
        }
        else {
            $filePath = sprintf('%s%s.%s', $img->folder, $img->filename, $img->ext);
        }

        return $filePath;
    }
}
