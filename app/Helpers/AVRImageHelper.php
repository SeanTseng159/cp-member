<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 9:42
 */

namespace App\Helpers;

use App\Models\AVR\Image;
use App\Repositories\AVR\ImageRepository;
use App\Services\AVR\ImageService;


Class AVRImageHelper extends BaseImageHelper
{
    protected static function getInstance()
    {
        if (is_null(static::$imageService)) {
            static::$imageService = new ImageService(new ImageRepository(new Image));
        }
        return static::$imageService;
    }

    protected static function getHost()
    {
        return CommonHelper::getAdHost();
    }

}
