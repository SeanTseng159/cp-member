<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 1:53
 */

namespace Ksd\Mediation\Result;

class Collection
{
    public $total;

    public function __construct($result = [], $parameter)
    {
        $this->result = $result;
        $this->total = count($result);
        $this->parameter = $parameter;
    }

    public function pagination()
    {
        $this->result = array_slice($this->result, $this->parameter->offset(), $this->parameter->limit);
        return $this;
    }

    public function sort()
    {
        usort($this->result, [$this->parameter, 'sort']);
        return $this;
    }

    public function all()
    {
        return $this->result;
    }
}