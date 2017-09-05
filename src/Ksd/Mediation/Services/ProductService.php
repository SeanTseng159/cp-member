<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 10:03
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Parameter\Product\AllParameter;
use Ksd\Mediation\Parameter\Product\TagsParameter;
use Ksd\Mediation\Repositories\ProductRepository;

class ProductService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    public function categories($id = 1)
    {
        return $this->repository->categories($id);
    }

    public function products($parameter)
    {
        $categories = [];
        if ($parameter instanceof AllParameter) {
            $categories = $parameter->tags;
        } else if($parameter instanceof TagsParameter) {
            $categories = $parameter->names;
        }
        $categoryIds = [];
        if(!empty($categories)) {
            $categoryResult = $this->categories();
            foreach ($categories as $category) {
                $categoryIds = array_merge($categoryIds, $this->filterCategory($categoryResult, $category));
            }
        }
        return $this->repository->products($categoryIds, $parameter)->pagination()->sort()->all();
    }


    public function product($parameter)
    {
        return $this->repository->product($parameter);
    }

    private function filterCategory($categoryResult, $name)
    {
        $ids = [];
        foreach ($categoryResult as $categoryRow) {
            if (!empty($categoryRow)) {
                $row = $categoryRow->filterByName($name);
                if(!empty($row)) {
                    $ids[] = $row->id;
                }
            }
        }
        return $ids;
    }
}