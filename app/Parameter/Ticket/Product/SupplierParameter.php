<?php
namespace App\Parameter\Ticket\Product;

use App\Parameter\BaseParameter;

class SupplierParameter extends BaseParameter
{
    public $parameters;

    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function products()
    {
        $this->parameters['page'] = $this->page;
        $this->parameters['limit'] = $this->limit;

        return $this->parameters;
    }

	/*public function __construct($params)
    {
        $this->backendHost = (env('APP_ENV') === 'production' || env('APP_ENV') === 'beta') ? BaseConfig::BACKEND_HOST : BaseConfig::BACKEND_HOST_TEST;
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
                        'generalPath' => $this->backendHost . $item->imgs->first()['img_path'],
                        'thumbonallPath' => $this->backendHost . $item->imgs->first()['img_thumbnail_path'],
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
    }*/
}
