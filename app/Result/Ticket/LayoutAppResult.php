<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Config\BaseConfig;
use App\Result\BaseResult;

class LayoutAppResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得資料
     * @param $data
     */
    public function all($apps, $version)
    {
        $result['version'] = (string) $version;

        $result['apps'] = ($apps->isEmpty()) ? [] : $this->transformApps($apps);

        return $result;
    }

    /**
     * 取得apps
     * @param $apps
     */
    public function transformApps($apps)
    {
        $newApps = [];
        foreach ($apps as $app) {
            $newApps[] = $this->getApp($app);
        }

        return $newApps;
    }

    /**
     * 取得單一app結構
     * @param $app
     */
    public function getApp($app)
    {
        $app = $app->toArray();

        $result = new \stdClass;
        $result->id = $this->arrayDefault($app, 'id');
        $result->name = $this->arrayDefault($app, 'name');

        $icon_img = $this->arrayDefault($app, 'icon_img');
        $result->icon = ($icon_img) ? $this->backendHost . $icon_img : '';

        $auth_status = $this->arrayDefault($app, 'auth_status');
        $result->isNeedLogin = ($auth_status === 1);

        $result->link = $this->getLink($app);

        return $result;
    }

    /**
     * 取得連結
     */
    private function getLink($app)
    {
        $result = new \stdClass;
        $result->type = $this->arrayDefault($app, 'link_type');

        if ($result->type === 0) {
            $result->url = (string) $this->arrayDefault($app, 'link_app');
        }
        elseif ($result->type === 1) {
            $result->url = (string) $this->arrayDefault($app, 'link_web');
        }
        else {
            $result->url = (string) $this->arrayDefault($app, 'link_app_scheme');
        }

        return $result;
    }
}
