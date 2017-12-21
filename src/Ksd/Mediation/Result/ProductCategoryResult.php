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

    /**
     * 根據 名稱 取得分類
     * @param $name
     * @return $this|null
     */
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

    /**
     * 根據 id 取得分類
     * @param $id
     * @return $this|null
     */
    public function filterById($id)
    {
        if ($this->id == $id) {
            return $this;
        }
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $category =  $child->filterById($id);
                if(!empty($category)) {
                    return $category;
                }
            }
        }
        return null;
    }
}