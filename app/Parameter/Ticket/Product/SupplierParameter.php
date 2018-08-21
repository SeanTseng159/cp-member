<?php
namespace App\Parameter\Ticket\Product;

use App\Parameter\BaseParameter;

class SupplierParameter extends BaseParameter
{
    public $transformed_params;
    
	public function __construct($params)
    {
        $transformed_params = null;
        $total = $params['prods']->total();
        if ( $total > 0) {
            $params['prods']->each(function($item) use (&$transformed_params){
                $transformed_params[] = collect([
                    'source' => $item->source,
                    'id' => $item->prod_id,
                    'name' => $item->prod_name,
                    'price' => $item->prod_price_sticker,
                    'salePrice' => $item->prod_price_retail,
                    'characteristic' => $item->prod_short,
                    'category' => $item->product_tags->first()['tag_id'],
                    'storeName' => $item->prod_store,
                    'place' => $item->full_address,
                    'imagUrls' => collect([
                        'generalPath' => asset($item->imgs->first()['img_path']),
                        'thumbonallPath' => asset($item->imgs->first()['img_thumbnail_path']),
                    ])
                ]);
            });
        }
        $this->transformed_params = [
            'total' => $total,
            'supplier_name' => empty($params['supplier']) ? null : $params['supplier']->supplier_name,
            'prods' => $transformed_params
        ];
    }
    
    public function getTransformedParams()
    {
        return $this->transformed_params;
    }
}
