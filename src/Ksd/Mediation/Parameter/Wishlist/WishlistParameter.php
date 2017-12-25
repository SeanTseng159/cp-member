<?php

/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/15
 * Time: 上午 09:37
 */

namespace Ksd\Mediation\Parameter\Wishlist;

use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Parameter\BaseParameter;

class WishlistParameter extends BaseParameter
{
    private $magento;
    private $tpass;

    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($input, $parameters = null)
    {
        $this->request($parameters);
        parent::codeigniterRequest($input);
    }

    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request)
    {
        parent::laravelRequest($request);
        $this->no = $request->input('wishlistItemId');
        $this->source = $request->input('source');
    }


}