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
            $filePath = $img->folder . $img->filename . $img->ext;
        }
        elseif (Agent::isTablet()) {
            $filePath = $img->folder . $img->filename . $img->ext;
        }
        else {
            $filePath = $img->folder . $img->filename . $img->ext;
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
}
