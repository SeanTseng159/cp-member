<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/5
 * Time: 下午 12:05
 */


namespace App\Enum;


use BenSampo\Enum\Enum;


final class MissionFileType extends Enum

{
    const file = 'file';
    const image = 'image';
    const url = 'url';
    const recognition_id = 'recognition_id';
}