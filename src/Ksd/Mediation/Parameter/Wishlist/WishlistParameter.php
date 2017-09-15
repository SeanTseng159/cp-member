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

    public function codeigniterRequest($input, $parameters = null)
    {
        $this->request($parameters);
        parent::codeigniterRequest($input);
    }

    public function laravelRequest($no, $request = null)
    {
        parent::laravelRequest($request);
        $this->no = $no;
        $this->source = $request->input('source');
    }


}