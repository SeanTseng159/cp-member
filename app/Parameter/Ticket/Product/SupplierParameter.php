<?php
namespace App\Parameter\Ticket\Product;

use App\Parameter\BaseParameter;

class SupplierParameter extends BaseParameter
{
    public $transformed_params;
    
	public function __construct($params)
    {
        $transformed_params = null;
        if (empty($params)) return;
        $params->each(function($item) use (&$transformed_params){
            $transformed_params[] = collect([
                'id' => $item->prod_id,
                'name' => $item->prod_name,
                'price' => $item->prod_price_sticker,
                'salePrice' => $item->prod_price_retail,
                'characteristic' => $item->prod_short,
                'category' => $item->product_tags->first()['tag_id'],
                'storeName' => $item->prod_store,
                'place' => $item->prod_store,
                'imagUrls' => collect([
                    'generalPath' => $item->imgs->first()['img_path'],
                    'thumbonallPath' => $item->imgs->first()['img_thumbnail_path'],
                ])
            ]);
        });
        $this->transformed_params = $transformed_params;
    }
    
    public function getTransformedParams()
    {
        return $this->transformed_params;
    }
}
