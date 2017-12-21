<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/20
 * Time: 下午 3:49
 */

namespace Ksd\Mediation\Traits\Product;


trait Sort
{
    /**
     * 商品列表排序
     * @param $a
     * @param $b
     * @return int
     */
    public function sort($a, $b)
    {
        if ($this->sort == 'created_at') {
            $format = "Y-m-d H:i:s";

            $now = \DateTime::createFromFormat($format, $a->createdAt);
            $before = \DateTime::createFromFormat($format, $b->createdAt);
            $compare = $now->getTimestamp() - $before->getTimestamp();

            if ($compare == 0) {
                return 0;
            }
            if ($this->direction == 'desc') {
                return $compare ? 1 : -1;
            }
            return $compare ? -1 : 1;
        } else if ($this->sort == 'price') {
            if ($a->price == $b->price) {
                return 0;
            }
            if ($this->direction == 'desc') {
                return $a->price < $b->price ? 1 : -1;
            }
            return $a->price < $b->price ? -1 : 1;
        }
    }
}