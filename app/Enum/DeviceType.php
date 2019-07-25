<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class DeviceType extends Enum
{
    //1:all 2:android 3:ios 4.web
    const All = 1;
    const Android = 2;
    const IOs = 3;
    const Web = 4;
}
