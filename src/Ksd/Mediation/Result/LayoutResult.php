<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/30
 * Time: 上午 09:44
 */

namespace Ksd\Mediation\Result;

use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Config\ProjectConfig;

class LayoutResult
{
    use ObjectHelper;

    /**
     * 處理 cityPass Layout資料建置
     * @param $result
     * @param bool $isDetail
     */
    public function cityPass($result)
    {



                foreach ($this->arrayDefault($result, 'slide', []) as $item) {
                    $row = [];
                    $row['adId'] = $this->arrayDefault($item, 'adId');
                    $row['adName'] = $this->arrayDefault($item, 'adName');
                    $row['adLang'] = $this->arrayDefault($item, 'adLang');
                    $row['adImg'] = $this->arrayDefault($item, 'adImg');
                    $row['adLinkWeb'] = $this->arrayDefault($item, 'adLinkWeb');
                    $row['adLinkAppType'] = $this->arrayDefault($item, 'adLinkAppType');
                    $row['adLinkApp'] = $this->arrayDefault($item, 'adLinkApp');
                    $row['adLinkAppTagId'] = $this->arrayDefault($item, 'adLinkAppTagId');
                    $row['adLinkAppProdId'] = $this->arrayDefault($item, 'adLinkAppProdId');
                    $row['adStarttime'] = $this->arrayDefault($item, 'adStarttime');
                    $row['adEndtime'] = $this->arrayDefault($item, 'adEndtime');

                    $this->slide[] = $row;

                }


                foreach ($this->arrayDefault($result, 'banner', []) as $item) {
                    $row = [];
                    $row['adId'] = $this->arrayDefault($item, 'adId');
                    $row['adName'] = $this->arrayDefault($item, 'adName');
                    $row['adLang'] = $this->arrayDefault($item, 'adLang');
                    $row['adImg'] = $this->arrayDefault($item, 'adImg');
                    $row['adLinkWeb'] = $this->arrayDefault($item, 'adLinkWeb');
                    $row['adLinkAppType'] = $this->arrayDefault($item, 'adLinkAppType');
                    $row['adLinkApp'] = $this->arrayDefault($item, 'adLinkApp');
                    $row['adLinkAppTagId'] = $this->arrayDefault($item, 'adLinkAppTagId');
                    $row['adLinkAppProdId'] = $this->arrayDefault($item, 'adLinkAppProdId');
                    $row['adStarttime'] = $this->arrayDefault($item, 'adStarttime');
                    $row['adEndtime'] = $this->arrayDefault($item, 'adEndtime');

                    $this->banner[] = $row;

                }


                foreach ($this->arrayDefault($result, 'exploration', []) as $item) {
                    $row = [];
                    $row['name'] = $this->arrayDefault($item, 'name');
                    $row['imgPath'] = $this->arrayDefault($item, 'imgPath');
                    $row['tagId'] = $this->arrayDefault($item, 'tagId');
                    $row['tagName'] = $this->arrayDefault($item, 'tagName');


                    $this->exploration[] = $row;

                }



                foreach ($this->arrayDefault($result, 'customize', []) as $item) {
                    $row = [];
                    $row['id'] = $this->arrayDefault($item, 'id');
                    $row['name'] = $this->arrayDefault($item, 'name');
                    $row['items'] = $this->arrayDefault($item, 'items');
                    $this->customize[] = $row;

                }


    }

}