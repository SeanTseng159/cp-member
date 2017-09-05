<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 1:53
 */

namespace Ksd\Mediation\Result;


use function Sodium\compare;

class Collection
{

    public function __construct($input = [], $parameter)
    {
        $this->result = $input;
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