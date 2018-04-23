<?php

namespace App\Console\Commands\Carts\Classes\CleanExpiredCarts;

use Ksd\Mediation\Config\ProjectConfig;
use App\Console\Commands\Carts\Classes\CleanExpiredCarts\Abstraction\ExpiredCart;
use Ksd\Mediation\CityPass\Wishlist;

/**
 * Description of Citypass
 *
 * @author ksduser
 */
class Citypass extends ExpiredCart
{
    
    public function __construct($memberId)
    {
        parent::__construct();
        $this->setSource(ProjectConfig::CITY_PASS);
        $this->setMemberId($memberId);
    }
    
    public function cartItemIds()
    {
        $itemIds = [];
        foreach ($this->cartDetail->items as $item) {
            $itemIds[]  = $item->additionals['priceId'];
        }
        $this->setCartItemIds($itemIds);
    }
    
    public function validItems()
    {
        foreach ($this->cartDetail->items as &$item) {
            if ( ! $this->isValidProduct($item->statusDesc)) {
                unset($item);
            }
        }
    }
    
    public function isValidProduct($cartItemStatusDesc)
    {
        return empty($cartItemStatusDesc);
    }
    
    public function addWishlist()
    {
        foreach ($this->cartDetail->items as $item) {
           $parameter = new \stdClass();
           $parameter->source = $this->source;
           $parameter->no = $item->id; 
           (new Wishlist())->authorization($this->token)->add($parameter);
        }
    }
    
}
