<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 9:31
 */

namespace Ksd\Mediation\Result;


use Ksd\Mediation\Helper\ObjectHelper;

class ProductCategoryResult
{
    use ObjectHelper;

    public function magento($result)
    {
        $this->source = 'magento';
        $this->id = $this->arrayDefault($result, 'id');
        $this->name = $this->arrayDefault($result, 'name');
    }

    public function filterByName($name)
    {
        if ($this->name === $name) {
            return $this;
        }
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $category =  $child->filterByName($name);
                if(!empty($category)) {
                    return $category;
                }
            }
        }
        return null;
    }
}