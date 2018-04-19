<?php

namespace App\Console\Commands\Carts\Classes\CleanExpiredCarts;

use Ksd\Mediation\Config\ProjectConfig;
use App\Console\Commands\Carts\Classes\CleanExpiredCarts\Abstraction\ExpiredCart;
use Ksd\Mediation\Magento\Wishlist;
use Log;

/**
 * Description of Magento
 *
 * @author ksduser
 */
class Magento extends ExpiredCart
{
    
    public function __construct($memberId) 
    {
        parent::__construct();
        $this->setSource(ProjectConfig::MAGENTO);
        $this->setMemberId($memberId);
    }
    
    public function cartItemIds()
    {
        $itemIds = [];
        foreach ($this->cartDetail->items as $item) {
            $itemIds[]  = $item->id;
        }
        $this->setCartItemIds($itemIds);
    }
    
    public function validItems()
    {
        $parameter = new \stdClass();
        $parameter->source = $this->source;
        
        foreach ($this->cartDetail->items as &$item) {
            $parameter->no = $item->id;
            $product = $this->productService->product($parameter);
            if ( ! $this->isValidProduct($product->quantity, $product->saleStatusCode)) {
                unset($item);
            }
        }
    }
    
    public function isValidProduct($productQuantity, $productSaleStatusCode) 
    {
        return $productQuantity != 0 && $productSaleStatusCode == '11';
    }
    
    public function addWishlist()
    {
        foreach ($this->cartDetail->items as $item)
        {
            (new Wishlist())->userAuthorization($this->token)->add($this->mainItemId($item->id));
        }
    }
    
    private function mainItemId($itemId)
    {
        return explode('-', $itemId)[0];
    }
}
