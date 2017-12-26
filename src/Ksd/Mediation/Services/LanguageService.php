<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/12/26
 * Time: 下午 04:42
 */

namespace Ksd\Mediation\Services;

use Request;

class LanguageService
{
    protected $lang;

    public function __construct($lang = '')
    {
        $this->setLang($lang);
    }

    /**
     * 設定語系
     * @return mixed
     */
    public function setLang($lang)
    {
        // 先註解
        //if (!$lang) $lang = Request::header('Accept-Language');
        $this->lang = $lang ?: env('APP_LANG');

        return $this;
    }

    /**
     * 取得語系
     * @return mixed
     */
    public function getLang()
    {

        return $this->lang;
    }

}
