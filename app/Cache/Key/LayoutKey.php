<?php
/**
 * User: Lee
 * Date: 2018/03/08
 * Time: 下午 02:57
 */

namespace App\Cache\Key;

class LayoutKey
{
    const HOME_KEY = 'layout.home';
    const MENU_KEY = 'layout.menu';
    const ONE_MENU_KEY = 'layout.menu.%s';
    const CATEGORY_KEY = 'layout.category.%s';
    const CATEGORY_PRODUCTS_KEY = 'layout.categoryProducts.%s';
    const SUB_CATEGORY_PRODUCTS_KEY = 'layout.subCategoryProducts.%s';
    const SERVICE_APPS_KEY = 'layout.apps';
    const SERVICE_APPS_VERSION_KEY = 'layout.apps.%s';
}
