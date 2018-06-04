<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Config;

class BaseConfig
{
    # 主機
    const   BACKEND_HOST         = 'https://backend.citypass.tw/';   // 後台路徑
    const   BACKEND_HOST_TEST    = 'https://devbackend.citypass.tw/';   // 後台路徑
    # 來源
    const   SOURCE_TICKET        = 'ct_pass';   // 票卷
    const   SOURCE_COMMODITY     = 'magento';   // 實體商品
}
